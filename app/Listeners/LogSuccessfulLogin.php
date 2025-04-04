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
   
//      public function handle(Login $event)
//     {
//         try {
//             $macAddresses = $this->getAllMacAddresses();
            
//             Activity::create([
//                 'user_id' => $event->user->id,
//                 'activity_type' => 'Login',
//                 'activity_time' => now(),
//                 'device_lan_mac' => $macAddresses['lan'] ?? null,
//                 'device_wifi_mac' => $macAddresses['wifi'] ?? null,
//             ]);
            
//             Log::info("Login activity recorded for user: {$event->user->username}");
//         } catch (\Exception $e) {
//             Log::error("Failed to record login activity: " . $e->getMessage());
//         }
//     }

//     private function getAllMacAddresses()
//     {
//         $macAddresses = [
//             'lan' => null,
//             'wifi' => null
//         ];
    
//         if (PHP_OS_FAMILY === 'Windows') {
//             $connections = [];
//             exec('wmic nic where (NetConnectionStatus=2) get MACAddress,NetConnectionID 2>&1', $connections);
            
//             foreach ($connections as $conn) {
//                 if (preg_match('/^([0-9A-F]{2}(?:[:-][0-9A-F]{2}){5})\s+(\S+)/i', trim($conn), $matches)) {
//                     $mac = strtoupper(str_replace('-', ':', $matches[1]));
//                     $interface = $matches[2];
                    
//                     if (stripos($interface, 'Wi-Fi') !== false || 
//                         stripos($interface, 'Wireless') !== false ||
//                         stripos($interface, 'WLAN') !== false) {
//                         $macAddresses['lan'] = $mac;
//                     } else {
//                         $macAddresses['wifi'] = $mac;
//                     }
//                 }
//             }
            
//             if (is_null($macAddresses['wifi']) && is_null($macAddresses['wifi'])) {
//                 exec('getmac /FO CSV /NH', $output);
//                 if (!empty($output)) {
//                     $parts = str_getcsv($output[0]);
//                     if (!empty($parts[0])) {
//                         $macAddresses['wifi'] = strtoupper(str_replace('-', ':', $parts[0]));
//                     }
//                 }
//             }
//         } else {
//             $ifconfig = shell_exec('ifconfig -a 2>/dev/null');
//             if (preg_match_all('/ether (([0-9a-f]{2}:){5}[0-9a-f]{2})/i', $ifconfig, $matches)) {
//                 $macs = array_map('strtoupper', $matches[1]);
                
//                 if (preg_match_all('/^(eth\d|en[a-z0-9]+):/im', $ifconfig, $ethMatches)) {
//                     $macAddresses['wifi'] = $macs[0] ?? null;
//                     $macAddresses['lan'] = $macs[1] ?? null;
//                 } elseif (preg_match_all('/^(wlan\d|wl[a-z0-9]+):/im', $ifconfig, $wifiMatches)) {
//                     $macAddresses['lan'] = $macs[0] ?? null;
//                     $macAddresses['wifi'] = $macs[1] ?? null;
//                 } else {
//                     $macAddresses['wifi'] = $macs[0] ?? null;
//                     if (count($macs) > 1) {
//                         $macAddresses['lan'] = $macs[1];
//                     }
//                 }
//             }
//         }
    
//         return $macAddresses;
//     }
 
// }
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
        // Debug info untuk melihat MAC yang terdeteksi
        Log::debug("Detected MAC addresses", $macAddresses);
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
                
                // PERBAIKAN: Deteksi jenis interface berdasarkan nama
                if (stripos($interface, 'Wi-Fi') !== false || 
                    stripos($interface, 'Wireless') !== false ||
                    stripos($interface, 'WLAN') !== false) {
                    $macAddresses['wifi'] = $mac; // Sekarang benar untuk WiFi
                } else {
                    $macAddresses['lan'] = $mac; // Sekarang benar untuk LAN
                }
            }
        }
        
        // Fallback jika wmic tidak bekerja
        if (is_null($macAddresses['lan']) && is_null($macAddresses['wifi'])) {
            exec('getmac /FO CSV /NH', $output);
            if (!empty($output)) {
                $parts = str_getcsv($output[0]);
                if (!empty($parts[0])) {
                    // Dalam VM, ini kemungkinan besar adalah LAN
                    $macAddresses['lan'] = strtoupper(str_replace('-', ':', $parts[0]));
                }
            }
        }
    } else {
        // Implementasi untuk Linux/MacOS dengan perbaikan
        $networkInterfaces = [];
        
        // Coba gunakan ip command (lebih modern) terlebih dahulu
        exec('ip -o link show | grep -v "lo:" | awk \'{print $2, $(NF-2)}\' 2>/dev/null', $networkInterfaces);
        
        if (empty($networkInterfaces)) {
            // Fallback ke ifconfig
            $ifconfig = shell_exec('ifconfig -a 2>/dev/null');
            preg_match_all('/^([a-z0-9]+).*?(?:ether|HWaddr) (([0-9a-f]{2}[:-]){5}[0-9a-f]{2})/ims', $ifconfig, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $interface = $match[1];
                $mac = strtoupper($match[2]);
                
                if (preg_match('/^(eth\d|en[a-z0-9]+|eth.+|vmnet.+)$/i', $interface)) {
                    $macAddresses['lan'] = $mac;
                } elseif (preg_match('/^(wlan\d|wl[a-z0-9]+|wifi.+)$/i', $interface)) {
                    $macAddresses['wifi'] = $mac;
                } elseif (empty($macAddresses['lan'])) { // Fallback jika belum ada lan
                    $macAddresses['lan'] = $mac;
                }
            }
        } else {
            // Process output dari ip command
            foreach ($networkInterfaces as $line) {
                if (preg_match('/^([^:]+):.*?([0-9A-F]{2}(?::[0-9A-F]{2}){5})$/i', $line, $match)) {
                    $interface = $match[1];
                    $mac = strtoupper($match[2]);
                    
                    if (preg_match('/^(eth\d|en[a-z0-9]+|eth.+|vmnet.+)$/i', $interface)) {
                        $macAddresses['lan'] = $mac;
                    } elseif (preg_match('/^(wlan\d|wl[a-z0-9]+|wifi.+)$/i', $interface)) {
                        $macAddresses['wifi'] = $mac;
                    } elseif (empty($macAddresses['lan'])) { // Fallback jika belum ada lan
                        $macAddresses['lan'] = $mac;
                    }
                }
            }
        }
        
        // Jika dalam VM dan tidak ada interface yang teridentifikasi sebagai WiFi,
        // kemungkinan besar semua interface adalah network adapter virtual (LAN)
        if (empty($macAddresses['lan']) && empty($macAddresses['wifi'])) {
            // Coba cek apakah ini VM dengan melihat dmesg output
            $isVM = false;
            exec('dmesg | grep -i virtual 2>/dev/null', $vmCheck);
            if (!empty($vmCheck)) {
                $isVM = true;
            }
            
            // Coba dapatkan setidaknya satu MAC address
            exec('cat /sys/class/net/*/address 2>/dev/null | grep -v "00:00:00:00:00:00" | head -1', $macOutput);
            if (!empty($macOutput) && isset($macOutput[0])) {
                $macAddresses['lan'] = strtoupper($macOutput[0]);
            }
        }
    }
    
    // Debug untuk melihat MAC yang terdeteksi
    Log::debug("Detected MAC Addresses", $macAddresses);

    return $macAddresses;
}
}