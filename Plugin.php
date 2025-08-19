<?php

namespace Skynettechnologies\Allinoneaccessibility;

use Backend\Facades\Backend;
use DateTime;
use DateTimeZone;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'All in One Accessibility',
            'description' => "Your website's accessibility can make or break your audience's experience. With hhh the **All in One Accessibility AI Free Accessibility Widget**, you can instantly boost your site's inclusivity and support over **140+ languages**! Experience the power of **23 essential features** in our free version and take the first step towards creating a better web for everyone.",
            'author'      => 'Skynet Technologies USA LLC',
            'icon'        => 'icon-universal-access'
        ];
    }

    public function registerNavigation()
    {
        return [
            'allinoneaccessibility' => [
                'label'       => 'All in One Accessibility',
                'url'         => Backend::url('skynettechnologies/allinoneaccessibility/dashboard'),
                'icon'        => 'icon-universal-access',
                'permissions' => ['skynettechnologies.allinoneaccessibility.*'],
                'order'       => 1000,
            ]
        ];
    }

    public function registerPermissions()
    {
        return [
            'skynettechnologies.allinoneaccessibility.*' => [
                'tab'   => 'All in One Accessibility',
                'label' => 'Access all plugin features'
            ]
        ];
    }
    public function boot()
    {
        if (!app()->runningInBackend()) {
            \Route::get('/allinoneaccessibility/widget', function () {
                return view('skynettechnologies.allinoneaccessibility::widget');
            });
            \Route::post('/allinoneaccessibility/fetch-widget-settings', function (\Illuminate\Http\Request $request) {
                return $this->fetchWidgetSettings($request);
            });

            \Event::listen('cms.page.beforeDisplay', function ($controller, $url, $page) {
                $scriptUrl = 'https://www.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js'
                    . '?colorcode=%23420083&token&position=bottom_right';

                $controller->addJs($scriptUrl);
            });
        }
    }

    public static function getWidgetInfo($req = null)
    {

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $dateTime = $now->format('Y-m-d\TH:i:sO');

        $websitename = request()->getHost();

        $post_fields = [
            'name' => $websitename,
            'email' => 'no-reply@' . base64_encode($websitename) . '.com',
            'company_name'     => '',
            'website'          => base64_encode($req['url']),
            'package_type'     => 'free-widget',
            'start_date'       => $dateTime,
            'end_date'         => '',
            'price'            => '',
            'discount_price'   => '0',
            'platform'         => 'Winter CMS',
            'api_key'          => '',
            'is_trial_period'  => '',
            'is_free_widget'   => '1',
            'bill_address'     => '',
            'country'          => '',
            'state'            => '',
            'city'             => '',
            'post_code'        => '',
            'transaction_id'   => '',
            'subscr_id'        => '',
            'payment_source'   => ''
        ];


        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://ada.skynettechnologies.us/api/add-user-domain',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_SSL_VERIFYPEER => false // ⚠️ Not for production
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }

    /**
     * Fetch widget settings from Skynet.
     */
    public static function fetchWidgetSettings($req)
    {

        // self::initNameless(); // Validate user
        $host = $req['url'];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://ada.skynettechnologies.us/api/widget-settings',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['website_url' => $host],
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $res = json_decode($response, true);

        if (empty($res['Data'])) {
            self::getWidgetInfo($req); // Register first if not found
            sleep(1); // Wait for sync
            return self::fetchWidgetSettings($req); // Retry
        }

        return json_encode($res['Data']);
    }
}
