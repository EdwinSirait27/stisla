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
            // Menggunakan output ipconfig /all yang lebih lengkap
            $ipconfig = shell_exec('ipconfig /all');
            
            // Parsing untuk Wireless adapter
            if (preg_match('/Wireless LAN adapter Wi-Fi.*?Physical Address.*?: (([0-9A-F]{2}-){5}[0-9A-F]{2})/is', $ipconfig, $matches)) {
                $macAddresses['wifi'] = strtoupper(str_replace('-', ':', $matches[1]));
            } elseif (preg_match('/Wi-Fi.*?Physical Address.*?: (([0-9A-F]{2}-){5}[0-9A-F]{2})/is', $ipconfig, $matches)) {
                $macAddresses['wifi'] = strtoupper(str_replace('-', ':', $matches[1]));
            } elseif (preg_match('/Wireless.*?Physical Address.*?: (([0-9A-F]{2}-){5}[0-9A-F]{2})/is', $ipconfig, $matches)) {
                $macAddresses['wifi'] = strtoupper(str_replace('-', ':', $matches[1]));
            }
            
            // Parsing untuk Ethernet/LAN adapter
            if (preg_match('/Ethernet adapter.*?Physical Address.*?: (([0-9A-F]{2}-){5}[0-9A-F]{2})/is', $ipconfig, $matches)) {
                $macAddresses['lan'] = strtoupper(str_replace('-', ':', $matches[1]));
            } elseif (preg_match('/Local Area Connection.*?Physical Address.*?: (([0-9A-F]{2}-){5}[0-9A-F]{2})/is', $ipconfig, $matches)) {
                $macAddresses['lan'] = strtoupper(str_replace('-', ':', $matches[1]));
            }
            
            // Fallback ke metode wmic jika ipconfig tidak berhasil
            if (is_null($macAddresses['wifi']) || is_null($macAddresses['lan'])) {
                $connections = [];
                exec('wmic nic where (NetConnectionStatus=2) get MACAddress,NetConnectionID 2>&1', $connections);
                
                foreach ($connections as $conn) {
                    if (preg_match('/^([0-9A-F]{2}(?:[:-][0-9A-F]{2}){5})\s+(\S+.*?)$/i', trim($conn), $matches)) {
                        $mac = strtoupper(str_replace('-', ':', $matches[1]));
                        $interface = $matches[2];
                        
                        if (stripos($interface, 'Wi-Fi') !== false || 
                            stripos($interface, 'Wireless') !== false ||
                            stripos($interface, 'WLAN') !== false) {
                            $macAddresses['wifi'] = $mac;
                        } elseif (empty($macAddresses['lan'])) {
                            $macAddresses['lan'] = $mac;
                        }
                    }
                }
            }
            
            // Fallback terakhir jika masih tidak ada hasil
            if (is_null($macAddresses['wifi']) && is_null($macAddresses['lan'])) {
                exec('getmac /FO CSV /NH', $output);
                if (!empty($output)) {
                    $parts = str_getcsv($output[0]);
                    if (!empty($parts[0])) {
                        $macAddresses['lan'] = strtoupper(str_replace('-', ':', $parts[0]));
                    }
                }
            }
        } else {
            // Implementasi untuk Linux/MacOS
            // Coba ambil semua interface dan MAC address
            $interfaces = [];
            
            // Periksa di /sys/class/net (lebih aman)
            if (is_dir('/sys/class/net')) {
                $netInterfaces = scandir('/sys/class/net');
                foreach ($netInterfaces as $iface) {
                    if ($iface !== '.' && $iface !== '..' && $iface !== 'lo') {
                        $macPath = "/sys/class/net/$iface/address";
                        if (file_exists($macPath) && is_readable($macPath)) {
                            $mac = trim(file_get_contents($macPath));
                            if ($mac && $mac !== '00:00:00:00:00:00') {
                                $interfaces[$iface] = strtoupper($mac);
                            }
                        }
                    }
                }
            }
            
            // Jika tidak berhasil dengan /sys/class/net, coba dengan ip command
            if (empty($interfaces)) {
                exec('ip -o link show | grep -v "lo:" | awk \'{print $2, $(NF-2)}\' 2>/dev/null', $ipOutput);
                foreach ($ipOutput as $line) {
                    if (preg_match('/^([^:]+):.*?([0-9a-f]{2}(?::[0-9a-f]{2}){5})$/i', $line, $match)) {
                        $iface = $match[1];
                        $mac = strtoupper($match[2]);
                        $interfaces[$iface] = $mac;
                    }
                }
            }
            
            // Fallback ke ifconfig jika ip command tidak berhasil
            if (empty($interfaces)) {
                $ifconfig = shell_exec('ifconfig -a 2>/dev/null');
                preg_match_all('/^([a-z0-9]+).*?(?:ether|HWaddr) (([0-9a-f]{2}[:-]){5}[0-9a-f]{2})/ims', $ifconfig, $matches, PREG_SET_ORDER);
                
                foreach ($matches as $match) {
                    $interfaces[$match[1]] = strtoupper($match[2]);
                }
            }
            
            // Identifikasi WiFi vs LAN berdasarkan nama interface
            foreach ($interfaces as $iface => $mac) {
                if (preg_match('/^(wlan\d|wl[a-z0-9]+|wifi|wlp|wlx)/i', $iface)) {
                    $macAddresses['wifi'] = $mac;
                } elseif (preg_match('/^(eth\d|en[a-z0-9]+|eth.+|enp|ens|enx)/i', $iface)) {
                    $macAddresses['lan'] = $mac;
                }
            }
            
            // Fallback - jika ada interface yang tidak teridentifikasi
            if (empty($macAddresses['lan']) && !empty($interfaces)) {
                // Prioritaskan interface yang bukan WiFi untuk LAN
                foreach ($interfaces as $iface => $mac) {
                    if (!preg_match('/^(wlan\d|wl[a-z0-9]+|wifi)/i', $iface)) {
                        $macAddresses['lan'] = $mac;
                        break;
                    }
                }
            }
        }
        
        // Debug log
        Log::debug("Detected MAC Addresses", $macAddresses);
    
        return $macAddresses;
    }
}