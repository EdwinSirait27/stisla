<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditedFingerprint extends Model
{
 

 protected $connection = 'mysql_second'; // koneksi ke database kedua
    protected $table = 'edited_fingerprint';   
    protected $fillable = ['pin',
        'employee_name',
        'position_name',
        'store_name',
        'scan_date',
        'duration',
        'in_1', 'device_1',
        'in_2', 'device_2',
        'in_3', 'device_3',
        'in_4', 'device_4',
        'in_5', 'device_5',
        'in_6', 'device_6',
        'in_7', 'device_7',
        'in_8', 'device_8',
        'in_9', 'device_9',
        'in_10', 'device_10',
        'attachment',];
}

   // use HasFactory;
//      protected $table = 'edited_fingerprints';
//   protected $fillable = [
//         'pin',
//         'employee_name',
//         'position_name',
//         'store_name',
//         'scan_date',
//         'duration',
//         'in_1', 'device_1',
//         'in_2', 'device_2',
//         'in_3', 'device_3',
//         'in_4', 'device_4',
//         'in_5', 'device_5',
//         'in_6', 'device_6',
//         'in_7', 'device_7',
//         'in_8', 'device_8',
//         'in_9', 'device_9',
//         'in_10', 'device_10',
//         'attachment',
//     ];

// 2222
// 2224
// 2227
// 2231
// 2234
// 2249
// 2250
// 2258
// 2259
// 2260
// 2261
// 2263
// 2265
// 2266
// 2267
// 2268
// 2274
// 2275
// 2276
// 2289
// 2290
// 2296
// 2300
// 2301
// 2306
// 2307
// 2309
// 2313
// 2326
// 2332
// 2341
// 2351
// 2355
// 2358
// 2359
// 2365
// 2373
// 2374
// 2376
// 2377
// 2381
// 2447
// 2463
// 2478
// 2480
// 2483
// 2486
// 2489
// 2490
// 2503
// 2533
// 2539
// 2544
// 2546
// 2550
// 2552
// 2555
// 2556
// 2558
// 2563
// 2564
// 2570
// 2572
// 2574
// 2577
// 2582
// 2591
// 2594
// 2600
// 2607
// 2609
// 2612
// 2615
// 2621
// 2625
// 2630
// 2631
// 2633
// 2635
// 2637
// 2638
// 2639
// 2640
// 2641
// 2642
// 2644
// 2647
// 2648
// 2649
// 2652
// 2654
// 2658
// 2659
// 2661
// 2662
// 2663
// 2665
// 2666
// 2668
// 2672
// 2675
// 2677
// 2678
// 2682
// 2685
// 2686
// 2687
// 2689
// 2691
// 2692
// 2693
// 2695
// 2696
// 2697
// 2698
// 2700
// 2702
// 2703
// 2704
// 2705
// 2709
// 2710
// 2711
// 2712
// 2713
// 2714
// 2715
// 2716
// 2717
// 2718
// 2719
// 2720
// 2721
// 2722
// 2723
// 2724
// 2725
// 2726
// 2727
// 2729
// 2731
// 2733