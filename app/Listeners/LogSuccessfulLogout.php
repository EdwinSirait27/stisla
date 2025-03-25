<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Activity;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $device_lan_mac = null;
        $device_wifi_mac = null;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            @exec('ipconfig /all', $output);

            $connectionType = $this->detectWindowsConnectionType($output);
            $macAddress = $this->parseWindowsMacAddress($output, $connectionType);

            if ($connectionType === 'wifi') {
                $device_wifi_mac = $macAddress;
            } elseif ($connectionType === 'lan') {
                $device_lan_mac = $macAddress;
            }
        } else {
            // Implementasi untuk Linux/Mac (sama seperti sebelumnya)
            $activeMac = $this->getActiveLinuxMac();
            if ($activeMac) {
                if ($this->isLinuxWifiConnection()) {
                    $device_wifi_mac = $activeMac;
                } else {
                    $device_lan_mac = $activeMac;
                }
            }
        }

        Activity::create([
            'user_id' => $event->user->id,
            'activity_type' => 'Logout',
            'activity_time' => now(),
            'device_lan_mac' => $device_lan_mac,
            'device_wifi_mac' => $device_wifi_mac,
        ]);
    }

    protected function detectWindowsConnectionType($output)
    {
        $wifiConnected = false;
        $lanConnected = false;
        $currentAdapter = null;

        foreach ($output as $line) {
            // Deteksi adapter baru
            if (preg_match('/^([^\s].*):$/', trim($line), $matches)) {
                $currentAdapter = trim($matches[1]);

                // Cek jika adapter WiFi
                if (str_contains($currentAdapter, 'Wireless LAN adapter Wi-Fi')) {
                    $wifiAdapter = $currentAdapter;
                }
                // Cek jika adapter Ethernet LAN
                elseif (str_contains($currentAdapter, 'Ethernet adapter Ethernet')) {
                    $lanAdapter = $currentAdapter;
                }
            }

            // Cek koneksi aktif
            if ($currentAdapter && preg_match('/IP(v4)? Address[\. ]+: ([\d\.]+)/', $line, $matches)) {
                if (isset($wifiAdapter) && $currentAdapter === $wifiAdapter) {
                    $wifiConnected = true;
                } elseif (isset($lanAdapter) && $currentAdapter === $lanAdapter) {
                    $lanConnected = true;
                }
            }
        }

        // Prioritaskan LAN jika keduanya terhubung
        if ($lanConnected)
            return 'lan';
        if ($wifiConnected)
            return 'wifi';
        return null;
    }

    protected function parseWindowsMacAddress($output, $type)
    {
        $currentAdapter = null;
        $targetAdapter = ($type === 'wifi')
            ? 'Wireless LAN adapter Wi-Fi'
            : 'Ethernet adapter Ethernet';

        foreach ($output as $line) {
            // Cari adapter target
            if (preg_match('/^([^\s].*):$/', trim($line), $matches)) {
                $currentAdapter = trim($matches[1]);
            }

            // Jika ini adapter yang kita cari
            if ($currentAdapter === $targetAdapter) {
                if (preg_match('/Physical Address[\. ]+: ([\w-]+)/', $line, $matches)) {
                    return strtoupper(str_replace('-', ':', $matches[1]));
                }
            }
        }

        return null;
    }

    // Fungsi untuk Linux/Mac tetap sama seperti sebelumnya
    protected function getActiveLinuxMac()
    {
        @exec("ip route show default | awk '/default/ {print $5}'", $interface);
        if (!empty($interface)) {
            $interface = $interface[0];
            @exec("cat /sys/class/net/$interface/address", $mac);
            if (!empty($mac)) {
                return strtoupper(trim($mac[0]));
            }
        }
        return null;
    }

    protected function isLinuxWifiConnection()
    {
        @exec("iwconfig 2>/dev/null | grep 'ESSID'", $wifiCheck);
        return !empty($wifiCheck);
    }
}
