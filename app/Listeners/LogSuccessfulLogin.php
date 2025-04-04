<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\Activity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

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
            
            // Deteksi jika berada di VM dan koreksi penempatan MAC address
            $isVM = $this->isRunningInVM();
            if ($isVM && !empty($macAddresses['lan']) && empty($macAddresses['wifi'])) {
                // Jika berada di VM dan hanya LAN yang terdeteksi, cek apakah IP menunjukkan koneksi WiFi
                $clientIP = Request::ip();
                
                // Cek apakah IP client adalah IP WiFi (bisa disesuaikan dengan range IP WiFi Anda)
                // Contoh: 192.168.50.x biasanya range WiFi
                if (strpos($clientIP, '192.168.50.') === 0) {
                    // Pindahkan MAC address ke wifi jika user kemungkinan terkoneksi via WiFi
                    $macAddresses['wifi'] = $macAddresses['lan'];
                    $macAddresses['lan'] = null;
                }
            }
            
            Activity::create([
                'user_id' => $event->user->id,
                'activity_type' => 'Login',
                'activity_time' => now(),
                'device_lan_mac' => $macAddresses['lan'] ?? null,
                'device_wifi_mac' => $macAddresses['wifi'] ?? null,
            ]);
            
            Log::info("Login activity recorded for user: {$event->user->username}");
            Log::debug("Detected MAC Addresses", $macAddresses);
            Log::debug("Client IP: " . Request::ip());
        } catch (\Exception $e) {
            Log::error("Failed to record login activity: " . $e->getMessage());
        }
    }
    
    /**
     * Deteksi apakah aplikasi berjalan di Virtual Machine
     */
    private function isRunningInVM()
    {
        // Beberapa tanda VM
        
        // 1. Cek MAC address dengan awalan yang biasa digunakan VM
        $vmMacPrefixes = ['52:54', '00:0C:29', '00:50:56', '00:16:3E', '00:03:FF'];
        
        try {
            // Untuk Windows
            if (PHP_OS_FAMILY === 'Windows') {
                $output = [];
                exec('getmac /FO CSV /NH', $output);
                foreach ($output as $line) {
                    $parts = str_getcsv($line);
                    if (!empty($parts[0])) {
                        $mac = strtoupper(str_replace('-', ':', $parts[0]));
                        foreach ($vmMacPrefixes as $prefix) {
                            if (strpos($mac, $prefix) === 0) {
                                return true;
                            }
                        }
                    }
                }
                
                // Cek nama model sistem
                $systemInfo = [];
                exec('systeminfo | findstr /B /C:"System Model"', $systemInfo);
                foreach ($systemInfo as $line) {
                    if (preg_match('/(VMware|Virtual|VirtualBox|KVM|Xen|Parallels)/i', $line)) {
                        return true;
                    }
                }
            } 
            // Untuk Linux
            else {
                // Cek dmesg
                exec('dmesg | grep -i virtual 2>/dev/null', $vmCheck);
                if (!empty($vmCheck)) {
                    return true;
                }
                
                // Cek /proc/cpuinfo
                if (file_exists('/proc/cpuinfo')) {
                    $cpuinfo = file_get_contents('/proc/cpuinfo');
                    if (preg_match('/(vmware|qemu|virtual|hypervisor)/i', $cpuinfo)) {
                        return true;
                    }
                }
                
                // Cek file /sys/class/dmi/id/product_name jika ada
                if (file_exists('/sys/class/dmi/id/product_name')) {
                    $product = trim(file_get_contents('/sys/class/dmi/id/product_name'));
                    if (preg_match('/(VMware|Virtual|VirtualBox|KVM|Xen)/i', $product)) {
                        return true;
                    }
                }
                
                // Cek MAC address
                $interfaces = glob('/sys/class/net/*/address');
                foreach ($interfaces as $interface) {
                    if (file_exists($interface)) {
                        $mac = trim(file_get_contents($interface));
                        foreach ($vmMacPrefixes as $prefix) {
                            if (strpos($mac, $prefix) === 0) {
                                return true;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error detecting VM: " . $e->getMessage());
        }
        
        return false;
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
                Log::debug("Found WiFi adapter via ipconfig: " . $macAddresses['wifi']);
            } elseif (preg_match('/Wi-Fi.*?Physical Address.*?: (([0-9A-F]{2}-){5}[0-9A-F]{2})/is', $ipconfig, $matches)) {
                $macAddresses['wifi'] = strtoupper(str_replace('-', ':', $matches[1]));
                Log::debug("Found WiFi adapter via generic pattern: " . $macAddresses['wifi']);
            }
            
            // Parsing untuk Ethernet/LAN adapter
            if (preg_match('/Ethernet adapter.*?Physical Address.*?: (([0-9A-F]{2}-){5}[0-9A-F]{2})/is', $ipconfig, $matches)) {
                $macAddresses['lan'] = strtoupper(str_replace('-', ':', $matches[1]));
                Log::debug("Found LAN adapter via ipconfig: " . $macAddresses['lan']);
            } elseif (preg_match('/Local Area Connection.*?Physical Address.*?: (([0-9A-F]{2}-){5}[0-9A-F]{2})/is', $ipconfig, $matches)) {
                $macAddresses['lan'] = strtoupper(str_replace('-', ':', $matches[1]));
                Log::debug("Found LAN adapter via generic pattern: " . $macAddresses['lan']);
            }
            
            // Fallback ke metode WMI jika ipconfig tidak berhasil
            if (is_null($macAddresses['wifi']) && is_null($macAddresses['lan'])) {
                Log::debug("Using WMI as fallback");
                $connections = [];
                exec('wmic nic where (NetConnectionStatus=2) get MACAddress,NetConnectionID 2>&1', $connections);
                
                foreach ($connections as $conn) {
                    Log::debug("WMI connection entry: " . $conn);
                    if (preg_match('/^([0-9A-F]{2}(?:[:-][0-9A-F]{2}){5})\s+(.+)$/i', trim($conn), $matches)) {
                        $mac = strtoupper(str_replace('-', ':', $matches[1]));
                        $interface = $matches[2];
                        
                        if (stripos($interface, 'Wi-Fi') !== false || 
                            stripos($interface, 'Wireless') !== false ||
                            stripos($interface, 'WLAN') !== false) {
                            $macAddresses['wifi'] = $mac;
                            Log::debug("Found WiFi adapter via WMI: {$interface} -> {$mac}");
                        } else {
                            $macAddresses['lan'] = $mac;
                            Log::debug("Found LAN adapter via WMI: {$interface} -> {$mac}");
                        }
                    }
                }
            }
        } else {
            // Implementasi untuk Linux/MacOS dengan prioritas akses langsung ke filesystem
            Log::debug("Detecting network interfaces on Linux/MacOS");
            
            // Periksa di /sys/class/net (lebih aman dan akurat)
            $interfaces = [];
            $activeInterfaces = [];
            
            if (is_dir('/sys/class/net')) {
                $netInterfaces = scandir('/sys/class/net');
                foreach ($netInterfaces as $iface) {
                    if ($iface !== '.' && $iface !== '..' && $iface !== 'lo') {
                        $macPath = "/sys/class/net/{$iface}/address";
                        $operstatePath = "/sys/class/net/{$iface}/operstate";
                        
                        if (file_exists($macPath) && is_readable($macPath)) {
                            $mac = trim(file_get_contents($macPath));
                            
                            // Cek status interface (up/down)
                            $isActive = false;
                            if (file_exists($operstatePath) && is_readable($operstatePath)) {
                                $operstate = trim(file_get_contents($operstatePath));
                                $isActive = ($operstate === 'up');
                                
                                if ($isActive) {
                                    $activeInterfaces[$iface] = $mac;
                                }
                            }
                            
                            if ($mac && $mac !== '00:00:00:00:00:00') {
                                $interfaces[$iface] = strtoupper($mac);
                                Log::debug("Found interface {$iface}: {$mac} " . ($isActive ? "(active)" : "(inactive)"));
                            }
                        }
                    }
                }
            }
            
            // Prioritaskan active interfaces
            if (!empty($activeInterfaces)) {
                Log::debug("Prioritizing active interfaces");
                foreach ($activeInterfaces as $iface => $mac) {
                    if (preg_match('/^(wlan\d|wl[a-z0-9]+|wifi|wlp|wlx)/i', $iface)) {
                        $macAddresses['wifi'] = $mac;
                        Log::debug("Assigned active WiFi interface: {$iface} -> {$mac}");
                    } elseif (preg_match('/^(eth\d|en[a-z0-9]+|eth.+|enp|ens|enx)/i', $iface)) {
                        $macAddresses['lan'] = $mac;
                        Log::debug("Assigned active LAN interface: {$iface} -> {$mac}");
                    }
                }
            }
            
            // Jika tidak ada interface aktif yang teridentifikasi, gunakan semua interface
            if (empty($macAddresses['wifi']) && empty($macAddresses['lan'])) {
                Log::debug("No active interfaces identified, using all available interfaces");
                foreach ($interfaces as $iface => $mac) {
                    if (preg_match('/^(wlan\d|wl[a-z0-9]+|wifi|wlp|wlx)/i', $iface)) {
                        $macAddresses['wifi'] = $mac;
                        Log::debug("Assigned WiFi interface: {$iface} -> {$mac}");
                    } elseif (preg_match('/^(eth\d|en[a-z0-9]+|eth.+|enp|ens|enx)/i', $iface)) {
                        $macAddresses['lan'] = $mac;
                        Log::debug("Assigned LAN interface: {$iface} -> {$mac}");
                    }
                }
            }
        }
        
        // Fallback - deteksi koneksi berdasarkan IP jika tidak ada WiFi yang terdeteksi
        if (empty($macAddresses['wifi']) && !empty($macAddresses['lan'])) {
            $clientIP = Request::ip();
            Log::debug("No WiFi detected, analyzing client IP: {$clientIP}");
            
            // Cek apakah IP client adalah IP WiFi (sesuaikan dengan range WiFi Anda)
            if (strpos($clientIP, '192.168.50.') === 0) {
                Log::debug("Client IP matches WiFi range, setting WiFi MAC from LAN");
                $macAddresses['wifi'] = $macAddresses['lan'];
                $macAddresses['lan'] = null;
            }
        }
        
        return $macAddresses;
    }
}