<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\Activity;
use Illuminate\Support\Facades\Log;

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
   
     public function handle(Login $event)
    {
        try {
            $macAddresses = $this->getAllMacAddresses();
            
            Activity::create([
                'user_id' => $event->user->id,
                'activity_type' => 'Login',
                'activity_time' => now(),
                'device_lan_mac' => $macAddresses['lan'] ?? null,
                'device_wifi_mac' => $macAddresses['wifi'] ?? null,
            ]);
            
            Log::info("Login activity recorded for user: {$event->user->username}");
        } catch (\Exception $e) {
            Log::error("Failed to record login activity: " . $e->getMessage());
        }
    }

    private function getAllMacAddresses()
    {
        $macAddresses = [
            'lan' => null,
            'wifi' => null
        ];
    
        if (PHP_OS_FAMILY === 'Windows') {
            // Deteksi MAC address menggunakan wmic (lebih akurat)
            $connections = [];
            exec('wmic nic where (NetConnectionStatus=2) get MACAddress,NetConnectionID 2>&1', $connections);
            
            foreach ($connections as $conn) {
                if (preg_match('/^([0-9A-F]{2}(?:[:-][0-9A-F]{2}){5})\s+(\S+)/i', trim($conn), $matches)) {
                    $mac = strtoupper(str_replace('-', ':', $matches[1]));
                    $interface = $matches[2];
                    
                    // Deteksi jenis interface berdasarkan nama
                    if (stripos($interface, 'Wi-Fi') !== false || 
                        stripos($interface, 'Wireless') !== false ||
                        stripos($interface, 'WLAN') !== false) {
                        $macAddresses['lan'] = $mac;
                    } else {
                        $macAddresses['wifi'] = $mac;
                    }
                }
            }
            
            // Fallback jika wmic tidak bekerja
            if (is_null($macAddresses['wifi']) && is_null($macAddresses['wifi'])) {
                exec('getmac /FO CSV /NH', $output);
                if (!empty($output)) {
                    $parts = str_getcsv($output[0]);
                    if (!empty($parts[0])) {
                        $macAddresses['wifi'] = strtoupper(str_replace('-', ':', $parts[0]));
                    }
                }
            }
        } else {
            // Implementasi untuk Linux/MacOS
            $ifconfig = shell_exec('ifconfig -a 2>/dev/null');
            if (preg_match_all('/ether (([0-9a-f]{2}:){5}[0-9a-f]{2})/i', $ifconfig, $matches)) {
                $macs = array_map('strtoupper', $matches[1]);
                
                // Coba bedakan berdasarkan nama interface
                if (preg_match_all('/^(eth\d|en[a-z0-9]+):/im', $ifconfig, $ethMatches)) {
                    $macAddresses['wifi'] = $macs[0] ?? null;
                    $macAddresses['lan'] = $macs[1] ?? null;
                } elseif (preg_match_all('/^(wlan\d|wl[a-z0-9]+):/im', $ifconfig, $wifiMatches)) {
                    $macAddresses['lan'] = $macs[0] ?? null;
                    $macAddresses['wifi'] = $macs[1] ?? null;
                } else {
                    // Fallback jika tidak bisa dibedakan
                    $macAddresses['wifi'] = $macs[0] ?? null;
                    if (count($macs) > 1) {
                        $macAddresses['lan'] = $macs[1];
                    }
                }
            }
        }
    
        return $macAddresses;
    }
 
}
