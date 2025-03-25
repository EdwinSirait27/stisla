<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Activity;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
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
    // public function handle($event)
    // {

    //     Activity::create([
    //         'user_id' => $event->user->id,
    //         'activity_type' => 'Login',
    //         'activity_time' => now(),
    //     ]);
    // }
    // public function handle($event)
    // {
    //     $device_lan_mac = null;
    //     $device_wifi_mac = null;

    //     // Deteksi koneksi aktif dan MAC address-nya
    //     if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    //         @exec('ipconfig /all', $output);

    //         $activeConnection = $this->getActiveWindowsConnection($output);

    //         if ($activeConnection) {
    //             if (str_contains($activeConnection['description'], 'Wireless') || 
    //                 str_contains($activeConnection['description'], 'Wi-Fi')) {
    //                 $device_wifi_mac = $activeConnection['mac'];
    //             } else {
    //                 $device_lan_mac = $activeConnection['mac'];
    //             }
    //         }
    //     } else {
    //         // Implementasi untuk Linux/Mac
    //         $activeMac = $this->getActiveLinuxMac();
    //         if ($activeMac) {
    //             if ($this->isLinuxWifiConnection()) {
    //                 $device_wifi_mac = $activeMac;
    //             } else {
    //                 $device_lan_mac = $activeMac;
    //             }
    //         }
    //     }

    //     Activity::create([
    //         'user_id' => $event->user->id,
    //         'activity_type' => 'Login',
    //         'activity_time' => now(),
    //         'device_lan_mac' => $device_lan_mac,
    //         'device_wifi_mac' => $device_wifi_mac,
    //     ]);
    // }

    // protected function getActiveWindowsConnection($output)
    // {
    //     $currentInterface = null;
    //     $connections = [];

    //     foreach ($output as $line) {
    //         // Deteksi awal interface baru
    //         if (preg_match('/^([^\s].*):$/', $line, $matches)) {
    //             $currentInterface = trim($matches[1]);
    //             $connections[$currentInterface] = [
    //                 'description' => '',
    //                 'mac' => null,
    //                 'status' => 'disconnected'
    //             ];
    //         } elseif ($currentInterface) {
    //             // Parse detail interface
    //             if (preg_match('/Description[\. ]+: (.+)/', $line, $matches)) {
    //                 $connections[$currentInterface]['description'] = trim($matches[1]);
    //             } elseif (preg_match('/Physical Address[\. ]+: ([\w-]+)/', $line, $matches)) {
    //                 $connections[$currentInterface]['mac'] = strtoupper(str_replace('-', ':', $matches[1]));
    //             } elseif (preg_match('/Media State[\. ]+: (.+)/', $line, $matches)) {
    //                 $connections[$currentInterface]['status'] = trim($matches[1]);
    //             } elseif (preg_match('/IP(v4)? Address[\. ]+: ([\d\.]+)/', $line, $matches)) {
    //                 $connections[$currentInterface]['status'] = 'connected';
    //             }
    //         }
    //     }

    //     // Cari interface yang terhubung dan memiliki IP
    //     foreach ($connections as $conn) {
    //         if ($conn['status'] === 'connected' && $conn['mac']) {
    //             return $conn;
    //         }
    //     }

    //     return null;
    // }

    // protected function getActiveLinuxMac()
    // {
    //     @exec("ip route show default | awk '/default/ {print $5}'", $interface);
    //     if (!empty($interface)) {
    //         $interface = $interface[0];
    //         @exec("cat /sys/class/net/$interface/address", $mac);
    //         if (!empty($mac)) {
    //             return strtoupper(trim($mac[0]));
    //         }
    //     }
    //     return null;
    // }

    // protected function isLinuxWifiConnection()
    // {
    //     @exec("iwconfig 2>/dev/null | grep 'ESSID'", $wifiCheck);
    //     return !empty($wifiCheck);
    // }
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
            'activity_type' => 'Login',
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
