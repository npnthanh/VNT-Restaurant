<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Staff;
use App\Models\WebsiteSetting;
use App\Services\WebsiteSettings;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Isolate these tests from the restaurant's existing MySQL database.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        (require database_path('migrations/2026_10_12_000000_create_website_settings_table.php'))->up();
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location_code');
            $table->string('phone');
            $table->string('password');
            $table->unsignedBigInteger('role_id');
        });
        Schema::create('location', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status');
        });
        Schema::create('promotion', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date')->nullable();
        });
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->boolean('is_featured');
            $table->timestamp('published_at')->nullable();
        });
        Storage::fake('website');
    }

    private function staff(string $roleName = 'Admin'): Staff
    {
        $role = Role::create(['name' => $roleName]);

        return Staff::create([
            'name' => 'Website Admin', 'location_code' => 'TEST', 'phone' => '0901234567',
            'password' => Hash::make('test-password'), 'role_id' => $role->id,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        $banners = array_map(fn ($banner) => [
            'id' => $banner['id'], 'enabled' => $banner['id'] === 'default-1', 'alt' => $banner['alt'],
        ], config('website.defaults.banners'));
        // Short aliases keep the contact/image regression cases readable.
        foreach ($overrides as $key => $value) {
            if (preg_match('/^(reset_)?banner_(\d+)_(desktop|mobile|enabled|alt)$/', $key, $matches)) {
                $banners[(int) $matches[2] - 1][($matches[1] ? 'reset_' : '').$matches[3]] = $value;
                unset($overrides[$key]);
            }
        }
        return array_replace([
            'site_name' => 'Nhà hàng mới', 'hotline' => '+84 901 234 567', 'email' => 'hello@example.com',
            'address' => '123 Đường mới', 'copyright' => '© Nhà hàng mới',
            'facebook' => 'https://facebook.com/new-restaurant', 'youtube' => null,
            'banners' => $banners,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_standalone_login_and_cannot_save(): void
    {
        $this->get('/admin/website-settings')->assertRedirect(route('admin.login'));
        $this->put('/admin/website-settings', $this->payload())->assertRedirect(route('admin.login'));
        $this->assertSame([], WebsiteSetting::find(1)->data);
        $this->get('/admin/login')->assertOk()->assertSee('Đăng nhập quản trị')->assertDontSee('header-top');
    }

    public function test_staff_without_permission_cannot_read_or_change_settings(): void
    {
        $this->actingAs($this->staff('Nhân Viên Bàn'), 'staff');
        $this->get('/admin/website-settings')->assertForbidden();
        $this->put('/admin/website-settings', $this->payload())->assertForbidden();
        $this->assertSame([], WebsiteSetting::find(1)->data);
    }

    public function test_admin_can_login_using_existing_account_and_manager_can_view_settings(): void
    {
        $this->staff();
        $this->post('/admin/login', ['location_code' => 'TEST', 'phone' => '0901234567', 'password' => 'test-password'])
            ->assertRedirect(route('admin.website-settings.edit'));
        $this->assertAuthenticated('staff');
        $this->get('/admin/website-settings')->assertOk()->assertSee('Cấu hình trang khách')->assertDontSee('nav-right');
        $this->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertGuest('staff');
        $this->actingAs($this->staff('Quản Lý'), 'staff')->get('/admin/website-settings')->assertOk();
    }

    public function test_failed_or_unauthorized_login_does_not_authenticate(): void
    {
        $this->staff('Nhân Viên Bàn');
        $this->post('/admin/login', ['location_code' => 'TEST', 'phone' => '0901234567', 'password' => 'incorrect'])
            ->assertSessionHasErrors('phone');
        $this->assertGuest('staff');
        $this->post('/admin/login', ['location_code' => 'TEST', 'phone' => '0901234567', 'password' => 'test-password'])
            ->assertSessionHasErrors('phone');
        $this->assertGuest('staff');
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        $credentials = ['location_code' => 'TEST', 'phone' => '0901234567', 'password' => 'incorrect'];
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', $credentials)->assertSessionHasErrors('phone');
        }
        $this->post('/admin/login', $credentials)->assertStatus(429);
    }

    public function test_saving_updates_public_header_footer_contact_and_enabled_banners(): void
    {
        $this->actingAs($this->staff(), 'staff');
        $this->put('/admin/website-settings', $this->payload([
            'logo' => UploadedFile::fake()->image('logo.png'),
            'banner_1_desktop' => UploadedFile::fake()->image('banner.jpg'),
            'hero_title' => "Tiêu đề mới\nDòng hai",
        ]))->assertRedirect(route('admin.website-settings.edit'))->assertSessionHas('success');
        $saved = WebsiteSetting::find(1)->data;
        Storage::disk('website')->assertExists(basename($saved['logo']));
        $this->assertNull($saved['banners'][0]['mobile']);
        $this->assertFalse($saved['banners'][1]['enabled']);
        $this->get('/contact')->assertOk()->assertSee('Nhà hàng mới')->assertSee('hello@example.com')
            ->assertSee('tel:+84901234567', false)->assertSee('123 Đường mới')->assertSee($saved['logo'], false)
            ->assertSee('https://facebook.com/new-restaurant', false)->assertDontSee('0961581328');
        $home = $this->get('/')->assertOk()->assertSee('Tiêu đề mới')->assertSee('Dòng hai')
            ->assertSee($saved['banners'][0]['desktop'], false)->assertDontSee('images/banner/Banner2.png', false)
            ->assertDontSee('aria-label="Banner tiếp theo"', false);
        $this->assertSame(1, substr_count($home->getContent(), 'class="slide"'));
    }

    public function test_replacing_and_resetting_images_cleans_up_only_uploaded_files(): void
    {
        $this->actingAs($this->staff(), 'staff');
        $this->put('/admin/website-settings', $this->payload([
            'logo' => UploadedFile::fake()->image('old.png'),
            'banner_1_desktop' => UploadedFile::fake()->image('desktop.png'),
            'banner_1_mobile' => UploadedFile::fake()->image('mobile.png'),
        ]))->assertSessionHasNoErrors();
        $previous = WebsiteSetting::find(1)->data;
        $this->put('/admin/website-settings', $this->payload([
            'logo' => UploadedFile::fake()->image('new.png'), 'reset_banner_1_desktop' => '1',
        ]))->assertSessionHasNoErrors();
        $saved = WebsiteSetting::find(1)->data;
        Storage::disk('website')->assertMissing(basename($previous['logo']));
        Storage::disk('website')->assertMissing(basename($previous['banners'][0]['desktop']));
        Storage::disk('website')->assertMissing(basename($previous['banners'][0]['mobile']));
        Storage::disk('website')->assertExists(basename($saved['logo']));
        $this->assertSame(config('website.defaults.banners.0.desktop'), $saved['banners'][0]['desktop']);
        $this->assertSame(config('website.defaults.banners.0.mobile'), $saved['banners'][0]['mobile']);
        $this->put('/admin/website-settings', $this->payload(['reset_logo' => '1']))->assertSessionHasNoErrors();
        Storage::disk('website')->assertMissing(basename($saved['logo']));
        $this->assertFileExists(public_path(config('website.defaults.logo')));
    }

    public function test_validation_rejects_unsafe_links_bad_contact_and_non_image_uploads_without_saving(): void
    {
        $this->actingAs($this->staff(), 'staff');
        $this->put('/admin/website-settings', $this->payload([
            'facebook' => 'javascript:alert(1)', 'email' => 'invalid', 'hotline' => 'invalid',
            'logo' => UploadedFile::fake()->create('payload.svg', 2, 'image/svg+xml'),
        ]))->assertSessionHasErrors(['facebook', 'email', 'hotline', 'logo']);
        $this->assertSame([], WebsiteSetting::find(1)->data);
        $this->put('/admin/website-settings', $this->payload(['logo' => UploadedFile::fake()->image('large.png')->size(5121)]))
            ->assertSessionHasErrors('logo');
        $this->assertSame([], Storage::disk('website')->allFiles());
        $this->put('/admin/website-settings', $this->payload(['banner_1_enabled' => '0']))->assertSessionHasErrors('banners');
        $this->assertSame([], WebsiteSetting::find(1)->data);
    }

    public function test_blank_social_links_are_hidden_and_home_content_is_html_escaped(): void
    {
        $this->actingAs($this->staff(), 'staff');
        $this->put('/admin/website-settings', $this->payload([
            'facebook' => '', 'instagram' => '', 'tiktok' => '', 'youtube' => '', 'zalo' => '',
            'hero_title' => '<script>alert(1)</script>', 'slogan_title' => '<img src=x onerror=alert(1)>',
        ]))->assertSessionHasNoErrors();
        $this->get('/')->assertOk()->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('aria-label="Facebook"', false)->assertDontSee('aria-label="YouTube"', false);
    }

    public function test_defaults_keep_public_pages_working_before_migration(): void
    {
        Schema::drop('website_settings');
        app(WebsiteSettings::class)->forget();
        $this->get('/contact')->assertOk()->assertSee('toibenquan@gmail.com')->assertSee('0961581328');
    }

    public function test_more_than_three_banners_can_be_saved_and_rendered_in_order(): void
    {
        $this->actingAs($this->staff(), 'staff');
        $rows = $this->payload()['banners'];
        foreach ($rows as &$row) $row['enabled'] = true;
        unset($row);
        for ($i = 4; $i <= 6; $i++) {
            $rows[] = ['id' => null, 'enabled' => true, 'alt' => "Banner {$i}", 'desktop' => UploadedFile::fake()->image("banner-{$i}.png")];
        }
        $this->put('/admin/website-settings', $this->payload(['banners' => $rows]))->assertSessionHasNoErrors();
        $saved = WebsiteSetting::find(1)->data['banners'];
        $this->assertCount(6, $saved);
        $this->assertCount(6, array_unique(array_column($saved, 'id')));
        foreach (array_slice($saved, 3) as $banner) {
            Storage::disk('website')->assertExists(basename($banner['desktop']));
            $this->assertNull($banner['mobile']);
        }
        $home = $this->get('/')->assertOk()->assertSeeInOrder(array_column($saved, 'desktop'), false);
        $this->assertSame(6, substr_count($home->getContent(), 'class="slide"'));
        $this->get('/admin/website-settings')->assertOk()->assertSee('Thêm banner')->assertSee('Banner 6');
    }

    public function test_reordering_and_removing_banners_keeps_files_and_cleans_removed_uploads(): void
    {
        $this->actingAs($this->staff(), 'staff');
        $rows = $this->payload()['banners'];
        $rows[] = ['id' => null, 'enabled' => true, 'alt' => 'Extra', 'desktop' => UploadedFile::fake()->image('extra.png'), 'mobile' => UploadedFile::fake()->image('extra-mobile.png')];
        $this->put('/admin/website-settings', $this->payload(['banners' => $rows]))->assertSessionHasNoErrors();
        $saved = WebsiteSetting::find(1)->data['banners'];
        $toInput = fn ($banner) => ['id' => $banner['id'], 'enabled' => true, 'alt' => $banner['alt']];
        // DOM order is submitted with stable, potentially non-sequential indices.
        $reordered = [9 => $toInput($saved[3]), 4 => $toInput($saved[2]), 0 => $toInput($saved[0])];
        $this->put('/admin/website-settings', $this->payload(['banners' => $reordered]))->assertSessionHasNoErrors();
        $actual = WebsiteSetting::find(1)->data['banners'];
        $this->assertSame([$saved[3]['id'], $saved[2]['id'], $saved[0]['id']], array_column($actual, 'id'));
        Storage::disk('website')->assertExists(basename($saved[3]['desktop']));
        Storage::disk('website')->assertExists(basename($saved[3]['mobile']));
        $this->put('/admin/website-settings', $this->payload(['banners' => [$toInput($saved[0])]]))->assertSessionHasNoErrors();
        Storage::disk('website')->assertMissing(basename($saved[3]['desktop']));
        Storage::disk('website')->assertMissing(basename($saved[3]['mobile']));
        $this->assertFileExists(public_path($saved[2]['desktop']));
    }

    public function test_existing_three_slot_configuration_is_preserved_when_upgrading(): void
    {
        WebsiteSetting::find(1)->update(['data' => [
            'banner_1_desktop' => 'images/banner/legacy.png', 'banner_1_mobile' => null,
            'banner_2_enabled' => false, 'banner_3_alt' => 'Mô tả cũ',
        ]]);
        $values = app(WebsiteSettings::class)->all();
        $this->assertSame('images/banner/legacy.png', $values['banners'][0]['desktop']);
        $this->assertNull($values['banners'][0]['mobile']);
        $this->assertFalse($values['banners'][1]['enabled']);
        $this->assertSame('Mô tả cũ', $values['banners'][2]['alt']);
        $this->actingAs($this->staff(), 'staff');
        $this->put('/admin/website-settings', $this->payload())->assertSessionHasNoErrors();
        $saved = WebsiteSetting::find(1)->data;
        $this->assertSame('images/banner/legacy.png', $saved['banners'][0]['desktop']);
        $this->assertArrayNotHasKey('banner_1_desktop', $saved);
    }

    public function test_invalid_new_banner_rolls_back_files_and_retains_submitted_rows(): void
    {
        $this->actingAs($this->staff(), 'staff');
        $rows = $this->payload()['banners'];
        $rows[] = ['id' => null, 'enabled' => true, 'alt' => 'Good new banner', 'desktop' => UploadedFile::fake()->image('good.png')];
        $rows[] = ['id' => null, 'enabled' => true, 'alt' => 'Missing image'];
        $this->from('/admin/website-settings')->put('/admin/website-settings', $this->payload(['banners' => $rows]))
            ->assertSessionHasErrors('banners.4.desktop');
        $this->assertSame([], WebsiteSetting::find(1)->data);
        $this->assertSame([], Storage::disk('website')->allFiles());
        $this->get('/admin/website-settings')->assertOk()->assertSee('Missing image')->assertSee('Banner 5');
        $this->put('/admin/website-settings', $this->payload(['banners' => []]))->assertSessionHasErrors('banners');
        $duplicate = [$this->payload()['banners'][0], $this->payload()['banners'][0]];
        $this->put('/admin/website-settings', $this->payload(['banners' => $duplicate]))->assertSessionHasErrors('banners.0.id');
        $unknown = [['id' => 'not-saved', 'enabled' => true]];
        $this->put('/admin/website-settings', $this->payload(['banners' => $unknown]))->assertSessionHasErrors('banners.0.id');
    }

    public function test_favicon_can_be_uploaded_replaced_and_reset_without_changing_logo(): void
    {
        $this->actingAs($this->staff(), 'staff');
        $this->get('/admin/website-settings')->assertOk()->assertSee('Favicon website');
        $this->put('/admin/website-settings', $this->payload([
            'logo' => UploadedFile::fake()->image('logo.png'),
            'favicon' => UploadedFile::fake()->image('icon.png', 64, 64),
        ]))->assertSessionHasNoErrors();
        $first = WebsiteSetting::find(1)->data;
        Storage::disk('website')->assertExists(basename($first['favicon']));
        $this->get('/contact')->assertOk()->assertSee('<link rel="icon" href="'.asset($first['favicon']).'">', false);
        $this->get('/admin/website-settings')->assertOk()->assertSee('<link rel="icon" href="'.asset($first['favicon']).'">', false);

        $this->put('/admin/website-settings', $this->payload([
            'favicon' => UploadedFile::fake()->createWithContent('icon.ico', file_get_contents(public_path('favicon-pos.ico'))),
        ]))->assertSessionHasNoErrors();
        $second = WebsiteSetting::find(1)->data;
        $this->assertStringEndsWith('.ico', $second['favicon']);
        Storage::disk('website')->assertMissing(basename($first['favicon']));
        Storage::disk('website')->assertExists(basename($second['favicon']));
        $this->assertSame($first['logo'], $second['logo']);

        $this->put('/admin/website-settings', $this->payload())->assertSessionHasNoErrors();
        $this->assertSame($second['favicon'], WebsiteSetting::find(1)->data['favicon']);
        $this->put('/admin/website-settings', $this->payload(['reset_favicon' => '1']))->assertSessionHasNoErrors();
        Storage::disk('website')->assertMissing(basename($second['favicon']));
        Storage::disk('website')->assertExists(basename($first['logo']));
        $this->assertSame(config('website.defaults.favicon'), WebsiteSetting::find(1)->data['favicon']);
        $this->assertFileExists(public_path(config('website.defaults.favicon')));
    }

    public function test_favicon_rejects_invalid_files_and_leaves_saved_settings_unchanged(): void
    {
        $this->actingAs($this->staff(), 'staff');
        $invalid = [
            UploadedFile::fake()->createWithContent('fake.ico', '<?php echo "not an icon";'),
            UploadedFile::fake()->createWithContent('icon.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            UploadedFile::fake()->image('large.png', 64, 64)->size(5121),
            UploadedFile::fake()->image('oversized.png', 1025, 64),
        ];
        foreach ($invalid as $file) {
            $this->put('/admin/website-settings', $this->payload(['favicon' => $file]))->assertSessionHasErrors('favicon');
            $this->assertSame([], WebsiteSetting::find(1)->data);
            $this->assertSame([], Storage::disk('website')->allFiles());
        }
    }
}
