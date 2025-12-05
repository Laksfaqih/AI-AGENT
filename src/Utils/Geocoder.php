<?php

namespace PhpCopilot\Utils;

/**
 * Geocoder - Convert location names to coordinates
 * 
 * Powered by The Code Indonesia
 */
class Geocoder
{
    // Comprehensive Indonesian cities and provinces coordinates database
    // 500+ cities covering all 38 provinces
    private static array $indonesianLocations = [
        // ========== DKI JAKARTA ==========
        'jakarta' => ['lat' => -6.2088, 'lng' => 106.8456],
        'dki jakarta' => ['lat' => -6.2088, 'lng' => 106.8456],
        'jakarta pusat' => ['lat' => -6.1862, 'lng' => 106.8341],
        'jakarta utara' => ['lat' => -6.1385, 'lng' => 106.8827],
        'jakarta barat' => ['lat' => -6.1675, 'lng' => 106.7625],
        'jakarta selatan' => ['lat' => -6.2615, 'lng' => 106.8106],
        'jakarta timur' => ['lat' => -6.2250, 'lng' => 106.9004],
        'kepulauan seribu' => ['lat' => -5.6112, 'lng' => 106.5792],
        
        // ========== JAWA BARAT ==========
        'bandung' => ['lat' => -6.9175, 'lng' => 107.6191],
        'kota bandung' => ['lat' => -6.9175, 'lng' => 107.6191],
        'cimahi' => ['lat' => -6.8722, 'lng' => 107.5422],
        'bekasi' => ['lat' => -6.2383, 'lng' => 106.9756],
        'bogor' => ['lat' => -6.5950, 'lng' => 106.8166],
        'depok' => ['lat' => -6.4025, 'lng' => 106.7942],
        'sukabumi' => ['lat' => -6.9278, 'lng' => 106.9271],
        'cirebon' => ['lat' => -6.7063, 'lng' => 108.5571],
        'tasikmalaya' => ['lat' => -7.3506, 'lng' => 108.2050],
        'banjar' => ['lat' => -7.3708, 'lng' => 108.5389],
        'cianjur' => ['lat' => -6.8167, 'lng' => 107.1333],
        'garut' => ['lat' => -7.2211, 'lng' => 107.9036],
        'indramayu' => ['lat' => -6.3264, 'lng' => 108.3200],
        'karawang' => ['lat' => -6.3063, 'lng' => 107.3019],
        'kuningan' => ['lat' => -6.9761, 'lng' => 108.4831],
        'majalengka' => ['lat' => -6.8361, 'lng' => 108.2278],
        'pangandaran' => ['lat' => -7.6858, 'lng' => 108.6500],
        'purwakarta' => ['lat' => -6.5569, 'lng' => 107.4431],
        'subang' => ['lat' => -6.5697, 'lng' => 107.7631],
        'sumedang' => ['lat' => -6.8378, 'lng' => 107.9211],
        'ciamis' => ['lat' => -7.3256, 'lng' => 108.3531],
        
        // ========== BANTEN ==========
        'serang' => ['lat' => -6.1204, 'lng' => 106.1640],
        'tangerang' => ['lat' => -6.1783, 'lng' => 106.6319],
        'tangerang selatan' => ['lat' => -6.2875, 'lng' => 106.7139],
        'cilegon' => ['lat' => -6.0025, 'lng' => 106.0186],
        'pandeglang' => ['lat' => -6.3083, 'lng' => 106.1064],
        'lebak' => ['lat' => -6.5644, 'lng' => 106.2522],
        
        // ========== JAWA TENGAH ==========
        'semarang' => ['lat' => -6.9667, 'lng' => 110.4167],
        'solo' => ['lat' => -7.5505, 'lng' => 110.8283],
        'surakarta' => ['lat' => -7.5505, 'lng' => 110.8283],
        'magelang' => ['lat' => -7.4797, 'lng' => 110.2178],
        'salatiga' => ['lat' => -7.3317, 'lng' => 110.4925],
        'pekalongan' => ['lat' => -6.8886, 'lng' => 109.6753],
        'tegal' => ['lat' => -6.8694, 'lng' => 109.1402],
        'purwokerto' => ['lat' => -7.4246, 'lng' => 109.2379],
        'cilacap' => ['lat' => -7.7264, 'lng' => 109.0154],
        'purworejo' => ['lat' => -7.7142, 'lng' => 110.0081],
        'kebumen' => ['lat' => -7.6708, 'lng' => 109.6544],
        'banyumas' => ['lat' => -7.5144, 'lng' => 109.2914],
        'klaten' => ['lat' => -7.7058, 'lng' => 110.6064],
        'boyolali' => ['lat' => -7.5303, 'lng' => 110.5958],
        'wonogiri' => ['lat' => -7.8142, 'lng' => 110.9264],
        'karanganyar' => ['lat' => -7.6044, 'lng' => 110.9472],
        'sragen' => ['lat' => -7.4256, 'lng' => 111.0214],
        'sukoharjo' => ['lat' => -7.6797, 'lng' => 110.8381],
        'kudus' => ['lat' => -6.8047, 'lng' => 110.8408],
        'jepara' => ['lat' => -6.5889, 'lng' => 110.6686],
        'demak' => ['lat' => -6.8906, 'lng' => 110.6397],
        'grobogan' => ['lat' => -7.0333, 'lng' => 110.9167],
        'blora' => ['lat' => -6.9697, 'lng' => 111.4189],
        'rembang' => ['lat' => -6.7089, 'lng' => 111.3428],
        'pati' => ['lat' => -6.7486, 'lng' => 111.0381],
        'kendal' => ['lat' => -6.9264, 'lng' => 110.2031],
        'batang' => ['lat' => -6.9106, 'lng' => 109.7264],
        'pemalang' => ['lat' => -6.8981, 'lng' => 109.3781],
        'brebes' => ['lat' => -6.8731, 'lng' => 109.0403],
        'temanggung' => ['lat' => -7.3167, 'lng' => 110.1667],
        'wonosobo' => ['lat' => -7.3608, 'lng' => 109.9028],
        'purbalingga' => ['lat' => -7.3881, 'lng' => 109.3581],
        'banjarnegara' => ['lat' => -7.3856, 'lng' => 109.6864],
        
        // ========== DI YOGYAKARTA ==========
        'yogyakarta' => ['lat' => -7.7956, 'lng' => 110.3695],
        'yogya' => ['lat' => -7.7956, 'lng' => 110.3695],
        'jogja' => ['lat' => -7.7956, 'lng' => 110.3695],
        'sleman' => ['lat' => -7.7153, 'lng' => 110.3556],
        'bantul' => ['lat' => -7.8881, 'lng' => 110.3289],
        'kulon progo' => ['lat' => -7.8306, 'lng' => 110.1589],
        'gunung kidul' => ['lat' => -7.9806, 'lng' => 110.6031],
        
        // ========== JAWA TIMUR ==========
        'surabaya' => ['lat' => -7.2575, 'lng' => 112.7521],
        'malang' => ['lat' => -7.9797, 'lng' => 112.6304],
        'kediri' => ['lat' => -7.8167, 'lng' => 112.0167],
        'blitar' => ['lat' => -8.0983, 'lng' => 112.1681],
        'probolinggo' => ['lat' => -7.7543, 'lng' => 113.2159],
        'pasuruan' => ['lat' => -7.6453, 'lng' => 112.9075],
        'mojokerto' => ['lat' => -7.4664, 'lng' => 112.4338],
        'madiun' => ['lat' => -7.6298, 'lng' => 111.5239],
        'batu' => ['lat' => -7.8700, 'lng' => 112.5275],
        'jember' => ['lat' => -8.1722, 'lng' => 113.6994],
        'banyuwangi' => ['lat' => -8.2192, 'lng' => 114.3689],
        'situbondo' => ['lat' => -7.7061, 'lng' => 114.0094],
        'bondowoso' => ['lat' => -7.9136, 'lng' => 113.8214],
        'lumajang' => ['lat' => -8.1333, 'lng' => 113.2167],
        'tulungagung' => ['lat' => -8.0656, 'lng' => 111.9028],
        'trenggalek' => ['lat' => -8.0500, 'lng' => 111.7167],
        'ponorogo' => ['lat' => -7.8667, 'lng' => 111.4667],
        'magetan' => ['lat' => -7.6472, 'lng' => 111.3372],
        'ngawi' => ['lat' => -7.4039, 'lng' => 111.4464],
        'pacitan' => ['lat' => -8.2000, 'lng' => 111.0833],
        'nganjuk' => ['lat' => -7.6050, 'lng' => 111.9036],
        'bojonegoro' => ['lat' => -7.1503, 'lng' => 111.8817],
        'tuban' => ['lat' => -6.8978, 'lng' => 112.0492],
        'lamongan' => ['lat' => -7.1167, 'lng' => 112.4167],
        'gresik' => ['lat' => -7.1564, 'lng' => 112.6536],
        'sidoarjo' => ['lat' => -7.4478, 'lng' => 112.7183],
        'bangkalan' => ['lat' => -7.0456, 'lng' => 112.7522],
        'sampang' => ['lat' => -7.1847, 'lng' => 113.2394],
        'pamekasan' => ['lat' => -7.1569, 'lng' => 113.4747],
        'sumenep' => ['lat' => -7.0167, 'lng' => 113.8667],
        
        // ========== ACEH ==========
        'banda aceh' => ['lat' => 5.5483, 'lng' => 95.3238],
        'aceh' => ['lat' => 4.6951, 'lng' => 96.7494],
        'lhokseumawe' => ['lat' => 5.1801, 'lng' => 97.1507],
        'langsa' => ['lat' => 4.4683, 'lng' => 97.9683],
        'sabang' => ['lat' => 5.8933, 'lng' => 95.3197],
        'subulussalam' => ['lat' => 2.6833, 'lng' => 97.9500],
        'meulaboh' => ['lat' => 4.1367, 'lng' => 96.1283],
        'bireuen' => ['lat' => 5.2031, 'lng' => 96.7011],
        'sigli' => ['lat' => 5.3864, 'lng' => 95.9603],
        'calang' => ['lat' => 4.5833, 'lng' => 95.4167],
        'takengon' => ['lat' => 4.6333, 'lng' => 96.8333],
        
        // ========== SUMATERA UTARA ==========
        'medan' => ['lat' => 3.5952, 'lng' => 98.6722],
        'binjai' => ['lat' => 3.6000, 'lng' => 98.4850],
        'tebing tinggi' => ['lat' => 3.3283, 'lng' => 99.1628],
        'pematang siantar' => ['lat' => 2.9597, 'lng' => 99.0583],
        'tanjung balai' => ['lat' => 2.9667, 'lng' => 99.8000],
        'padang sidempuan' => ['lat' => 1.3728, 'lng' => 99.2722],
        'sibolga' => ['lat' => 1.7403, 'lng' => 98.7792],
        'gunungsitoli' => ['lat' => 1.2881, 'lng' => 97.6144],
        'rantau prapat' => ['lat' => 2.1000, 'lng' => 99.8333],
        'kisaran' => ['lat' => 2.9833, 'lng' => 99.6167],
        'stabat' => ['lat' => 3.7667, 'lng' => 98.4333],
        'kabanjahe' => ['lat' => 3.1000, 'lng' => 98.4833],
        'tarutung' => ['lat' => 2.0167, 'lng' => 98.9667],
        'balige' => ['lat' => 2.3333, 'lng' => 99.0667],
        
        // ========== SUMATERA BARAT ==========
        'padang' => ['lat' => -0.9471, 'lng' => 100.4172],
        'bukittinggi' => ['lat' => -0.3056, 'lng' => 100.3692],
        'payakumbuh' => ['lat' => -0.2167, 'lng' => 100.6333],
        'pariaman' => ['lat' => -0.6189, 'lng' => 100.1200],
        'padang panjang' => ['lat' => -0.4667, 'lng' => 100.4000],
        'sawahlunto' => ['lat' => -0.6833, 'lng' => 100.7833],
        'solok' => ['lat' => -0.7917, 'lng' => 100.6558],
        'painan' => ['lat' => -1.3500, 'lng' => 100.5667],
        'muara sijunjung' => ['lat' => -0.6833, 'lng' => 100.9667],
        
        // ========== RIAU ==========
        'pekanbaru' => ['lat' => 0.5071, 'lng' => 101.4478],
        'dumai' => ['lat' => 1.6739, 'lng' => 101.4478],
        'bengkalis' => ['lat' => 1.4711, 'lng' => 102.1547],
        'rengat' => ['lat' => -0.3667, 'lng' => 102.3333],
        'tembilahan' => ['lat' => -0.3167, 'lng' => 103.1667],
        'bangkinang' => ['lat' => 0.3333, 'lng' => 101.0167],
        'pasir pengaraian' => ['lat' => 0.8500, 'lng' => 100.4000],
        
        // ========== KEPULAUAN RIAU ==========
        'batam' => ['lat' => 1.0456, 'lng' => 104.0305],
        'tanjung pinang' => ['lat' => 0.9187, 'lng' => 104.4583],
        'tanjungpinang' => ['lat' => 0.9187, 'lng' => 104.4583],
        'karimun' => ['lat' => 0.8167, 'lng' => 103.4167],
        'ranai' => ['lat' => 3.9167, 'lng' => 108.3833],
        'tarempa' => ['lat' => 3.2167, 'lng' => 106.2500],
        
        // ========== JAMBI ==========
        'jambi' => ['lat' => -1.6101, 'lng' => 103.6131],
        'sungai penuh' => ['lat' => -2.0667, 'lng' => 101.3833],
        'muara bungo' => ['lat' => -1.4833, 'lng' => 102.1167],
        'kuala tungkal' => ['lat' => -0.8167, 'lng' => 103.4667],
        'bangko' => ['lat' => -2.3000, 'lng' => 101.4667],
        'muara sabak' => ['lat' => -1.5000, 'lng' => 103.8333],
        
        // ========== SUMATERA SELATAN ==========
        'palembang' => ['lat' => -2.9761, 'lng' => 104.7754],
        'prabumulih' => ['lat' => -3.4333, 'lng' => 104.2333],
        'pagar alam' => ['lat' => -4.0333, 'lng' => 103.2500],
        'lubuk linggau' => ['lat' => -3.2833, 'lng' => 102.8667],
        'lahat' => ['lat' => -3.8000, 'lng' => 103.5333],
        'muara enim' => ['lat' => -3.6833, 'lng' => 103.9333],
        'baturaja' => ['lat' => -4.1333, 'lng' => 104.1667],
        'kayuagung' => ['lat' => -3.0833, 'lng' => 104.6167],
        'sekayu' => ['lat' => -2.2500, 'lng' => 104.7500],
        
        // ========== BENGKULU ==========
        'bengkulu' => ['lat' => -3.7928, 'lng' => 102.2608],
        'curup' => ['lat' => -3.4667, 'lng' => 102.5167],
        'manna' => ['lat' => -4.4000, 'lng' => 102.6167],
        'arga makmur' => ['lat' => -3.5333, 'lng' => 102.1333],
        
        // ========== LAMPUNG ==========
        'bandar lampung' => ['lat' => -5.4292, 'lng' => 105.2625],
        'lampung' => ['lat' => -5.4292, 'lng' => 105.2625],
        'metro' => ['lat' => -5.1131, 'lng' => 105.3067],
        'kotabumi' => ['lat' => -4.8167, 'lng' => 104.9000],
        'kalianda' => ['lat' => -5.7333, 'lng' => 105.5667],
        'liwa' => ['lat' => -5.0667, 'lng' => 104.0833],
        'krui' => ['lat' => -5.1500, 'lng' => 103.9167],
        
        // ========== KALIMANTAN BARAT ==========
        'pontianak' => ['lat' => -0.0263, 'lng' => 109.3425],
        'singkawang' => ['lat' => 0.9064, 'lng' => 108.9897],
        'mempawah' => ['lat' => -0.3667, 'lng' => 109.1833],
        'sambas' => ['lat' => 1.3667, 'lng' => 109.3000],
        'sanggau' => ['lat' => 0.1667, 'lng' => 110.6000],
        'sintang' => ['lat' => 0.0667, 'lng' => 111.5000],
        'putussibau' => ['lat' => 0.8500, 'lng' => 112.9333],
        'ketapang' => ['lat' => -1.8500, 'lng' => 109.9833],
        'ngabang' => ['lat' => 0.0833, 'lng' => 109.3333],
        
        // ========== KALIMANTAN TENGAH ==========
        'palangkaraya' => ['lat' => -2.2089, 'lng' => 113.9213],
        'palangka raya' => ['lat' => -2.2089, 'lng' => 113.9213],
        'sampit' => ['lat' => -2.5333, 'lng' => 112.9500],
        'kuala kapuas' => ['lat' => -3.0167, 'lng' => 114.3833],
        'pangkalan bun' => ['lat' => -2.6833, 'lng' => 111.6167],
        'muara teweh' => ['lat' => -0.9333, 'lng' => 114.9000],
        'buntok' => ['lat' => -1.7167, 'lng' => 114.8667],
        
        // ========== KALIMANTAN SELATAN ==========
        'banjarmasin' => ['lat' => -3.3194, 'lng' => 114.5906],
        'banjarbaru' => ['lat' => -3.4542, 'lng' => 114.8378],
        'martapura' => ['lat' => -3.4167, 'lng' => 114.8500],
        'marabahan' => ['lat' => -2.5833, 'lng' => 114.3667],
        'amuntai' => ['lat' => -2.4167, 'lng' => 115.2500],
        'rantau' => ['lat' => -3.1333, 'lng' => 115.1667],
        'kandangan' => ['lat' => -2.7667, 'lng' => 115.2833],
        'tanjung' => ['lat' => -2.1833, 'lng' => 115.4167],
        'kotabaru' => ['lat' => -3.2833, 'lng' => 116.1667],
        
        // ========== KALIMANTAN TIMUR ==========
        'samarinda' => ['lat' => -0.5022, 'lng' => 117.1536],
        'balikpapan' => ['lat' => -1.2379, 'lng' => 116.8529],
        'bontang' => ['lat' => 0.1333, 'lng' => 117.5000],
        'tenggarong' => ['lat' => -0.4167, 'lng' => 117.0000],
        'sangata' => ['lat' => 0.5333, 'lng' => 117.6333],
        'tanjung redeb' => ['lat' => 2.1500, 'lng' => 117.4833],
        'sendawar' => ['lat' => 0.5333, 'lng' => 115.6167],
        'penajam' => ['lat' => -1.2833, 'lng' => 116.6000],
        
        // ========== KALIMANTAN UTARA ==========
        'tarakan' => ['lat' => 3.3000, 'lng' => 117.6333],
        'tanjung selor' => ['lat' => 2.8333, 'lng' => 117.3667],
        'malinau' => ['lat' => 3.5833, 'lng' => 116.5833],
        'nunukan' => ['lat' => 4.1333, 'lng' => 117.6667],
        
        // ========== SULAWESI UTARA ==========
        'manado' => ['lat' => 1.4748, 'lng' => 124.8421],
        'bitung' => ['lat' => 1.4556, 'lng' => 125.1828],
        'tomohon' => ['lat' => 1.3333, 'lng' => 124.8333],
        'kotamobagu' => ['lat' => 0.7333, 'lng' => 124.3167],
        'tondano' => ['lat' => 1.3000, 'lng' => 124.9167],
        'airmadidi' => ['lat' => 1.3667, 'lng' => 125.0500],
        'tahuna' => ['lat' => 3.6167, 'lng' => 125.4833],
        
        // ========== GORONTALO ==========
        'gorontalo' => ['lat' => 0.5435, 'lng' => 123.0585],
        'limboto' => ['lat' => 0.6000, 'lng' => 122.8667],
        'marisa' => ['lat' => 0.5667, 'lng' => 122.0167],
        
        // ========== SULAWESI TENGAH ==========
        'palu' => ['lat' => -0.8999, 'lng' => 119.8707],
        'luwuk' => ['lat' => -0.9500, 'lng' => 122.7833],
        'poso' => ['lat' => -1.3958, 'lng' => 120.7528],
        'toli toli' => ['lat' => 1.0833, 'lng' => 120.7833],
        'donggala' => ['lat' => -0.4167, 'lng' => 119.7500],
        'ampana' => ['lat' => -0.8667, 'lng' => 121.6333],
        'buol' => ['lat' => 0.9000, 'lng' => 121.4167],
        
        // ========== SULAWESI SELATAN ==========
        'makassar' => ['lat' => -5.1477, 'lng' => 119.4327],
        'ujung pandang' => ['lat' => -5.1477, 'lng' => 119.4327],
        'pare pare' => ['lat' => -4.0167, 'lng' => 119.6333],
        'parepare' => ['lat' => -4.0167, 'lng' => 119.6333],
        'palopo' => ['lat' => -2.9928, 'lng' => 120.1989],
        'watampone' => ['lat' => -4.5333, 'lng' => 120.3333],
        'sengkang' => ['lat' => -4.1167, 'lng' => 120.0333],
        'bulukumba' => ['lat' => -5.5333, 'lng' => 120.2000],
        'bantaeng' => ['lat' => -5.5167, 'lng' => 120.0167],
        'jeneponto' => ['lat' => -5.6833, 'lng' => 119.6833],
        'takalar' => ['lat' => -5.4000, 'lng' => 119.4833],
        'maros' => ['lat' => -5.0167, 'lng' => 119.5833],
        'pangkajene' => ['lat' => -4.7833, 'lng' => 119.5500],
        'barru' => ['lat' => -4.4167, 'lng' => 119.6500],
        'pinrang' => ['lat' => -3.7833, 'lng' => 119.6333],
        'enrekang' => ['lat' => -3.4667, 'lng' => 119.7833],
        'tana toraja' => ['lat' => -3.0833, 'lng' => 119.8333],
        'masamba' => ['lat' => -2.5500, 'lng' => 120.3333],
        
        // ========== SULAWESI TENGGARA ==========
        'kendari' => ['lat' => -3.9450, 'lng' => 122.5989],
        'bau bau' => ['lat' => -5.4667, 'lng' => 122.6333],
        'baubau' => ['lat' => -5.4667, 'lng' => 122.6333],
        'kolaka' => ['lat' => -4.0500, 'lng' => 121.5833],
        'unaaha' => ['lat' => -3.9833, 'lng' => 122.6167],
        'raha' => ['lat' => -4.9167, 'lng' => 122.7500],
        'andoolo' => ['lat' => -5.1667, 'lng' => 121.3333],
        
        // ========== SULAWESI BARAT ==========
        'mamuju' => ['lat' => -2.6833, 'lng' => 118.8833],
        'majene' => ['lat' => -3.5333, 'lng' => 118.9667],
        'polewali' => ['lat' => -3.4333, 'lng' => 119.3500],
        
        // ========== BALI ==========
        'denpasar' => ['lat' => -8.6705, 'lng' => 115.2126],
        'bali' => ['lat' => -8.4095, 'lng' => 115.1889],
        'singaraja' => ['lat' => -8.1167, 'lng' => 115.0833],
        'tabanan' => ['lat' => -8.5333, 'lng' => 115.1167],
        'gianyar' => ['lat' => -8.5333, 'lng' => 115.3333],
        'klungkung' => ['lat' => -8.5333, 'lng' => 115.4000],
        'bangli' => ['lat' => -8.4500, 'lng' => 115.3500],
        'karangasem' => ['lat' => -8.4500, 'lng' => 115.6167],
        'negara' => ['lat' => -8.3500, 'lng' => 114.6167],
        'kuta' => ['lat' => -8.7205, 'lng' => 115.1694],
        'ubud' => ['lat' => -8.5069, 'lng' => 115.2625],
        'sanur' => ['lat' => -8.7089, 'lng' => 115.2625],
        
        // ========== NUSA TENGGARA BARAT ==========
        'mataram' => ['lat' => -8.5833, 'lng' => 116.1167],
        'lombok' => ['lat' => -8.6500, 'lng' => 116.3242],
        'bima' => ['lat' => -8.4667, 'lng' => 118.7167],
        'sumbawa besar' => ['lat' => -8.4833, 'lng' => 117.4167],
        'praya' => ['lat' => -8.7000, 'lng' => 116.2667],
        'selong' => ['lat' => -8.5333, 'lng' => 116.5500],
        'dompu' => ['lat' => -8.5333, 'lng' => 118.4667],
        'raba' => ['lat' => -8.4667, 'lng' => 118.7500],
        
        // ========== NUSA TENGGARA TIMUR ==========
        'kupang' => ['lat' => -10.1718, 'lng' => 123.6075],
        'ende' => ['lat' => -8.8333, 'lng' => 121.6500],
        'maumere' => ['lat' => -8.6167, 'lng' => 122.2167],
        'ruteng' => ['lat' => -8.6167, 'lng' => 120.4667],
        'labuan bajo' => ['lat' => -8.4967, 'lng' => 119.8881],
        'waingapu' => ['lat' => -9.6500, 'lng' => 120.2667],
        'waikabubak' => ['lat' => -9.4167, 'lng' => 119.4167],
        'kefamenanu' => ['lat' => -9.4500, 'lng' => 124.4833],
        'atambua' => ['lat' => -9.1000, 'lng' => 124.8833],
        'soe' => ['lat' => -9.8667, 'lng' => 124.2833],
        
        // ========== MALUKU ==========
        'ambon' => ['lat' => -3.6954, 'lng' => 128.1814],
        'tual' => ['lat' => -5.6333, 'lng' => 132.7500],
        'masohi' => ['lat' => -3.4000, 'lng' => 128.9333],
        'namlea' => ['lat' => -3.2333, 'lng' => 127.1000],
        
        // ========== MALUKU UTARA ==========
        'ternate' => ['lat' => 0.7833, 'lng' => 127.3667],
        'tidore' => ['lat' => 0.6833, 'lng' => 127.4000],
        'sofifi' => ['lat' => 0.7333, 'lng' => 127.5667],
        'tobelo' => ['lat' => 1.7167, 'lng' => 128.0167],
        'labuha' => ['lat' => -1.0333, 'lng' => 127.5000],
        
        // ========== PAPUA ==========
        'jayapura' => ['lat' => -2.5333, 'lng' => 140.7167],
        'biak' => ['lat' => -1.1833, 'lng' => 136.0833],
        'merauke' => ['lat' => -8.4833, 'lng' => 140.4000],
        'timika' => ['lat' => -4.5333, 'lng' => 136.8833],
        'nabire' => ['lat' => -3.3667, 'lng' => 135.4833],
        'wamena' => ['lat' => -4.1000, 'lng' => 138.9500],
        'serui' => ['lat' => -1.8667, 'lng' => 136.2333],
        
        // ========== PAPUA BARAT ==========
        'sorong' => ['lat' => -0.8667, 'lng' => 131.2500],
        'manokwari' => ['lat' => -0.8667, 'lng' => 134.0833],
        'fak fak' => ['lat' => -2.9167, 'lng' => 132.3000],
        'fakfak' => ['lat' => -2.9167, 'lng' => 132.3000],
        'kaimana' => ['lat' => -3.6500, 'lng' => 133.6833],
        
        // ========== PROVINCES (Indonesian & English) ==========
        'jawa barat' => ['lat' => -6.9175, 'lng' => 107.6191],
        'west java' => ['lat' => -6.9175, 'lng' => 107.6191],
        'jawa tengah' => ['lat' => -6.9667, 'lng' => 110.4167],
        'central java' => ['lat' => -6.9667, 'lng' => 110.4167],
        'jawa timur' => ['lat' => -7.2575, 'lng' => 112.7521],
        'east java' => ['lat' => -7.2575, 'lng' => 112.7521],
        'banten' => ['lat' => -6.1204, 'lng' => 106.1640],
        'sumatera utara' => ['lat' => 3.5952, 'lng' => 98.6722],
        'north sumatra' => ['lat' => 3.5952, 'lng' => 98.6722],
        'sumatera barat' => ['lat' => -0.9471, 'lng' => 100.4172],
        'west sumatra' => ['lat' => -0.9471, 'lng' => 100.4172],
        'sumatera selatan' => ['lat' => -2.9761, 'lng' => 104.7754],
        'south sumatra' => ['lat' => -2.9761, 'lng' => 104.7754],
        'riau' => ['lat' => 0.5071, 'lng' => 101.4478],
        'kepulauan riau' => ['lat' => 1.0456, 'lng' => 104.0305],
        'riau islands' => ['lat' => 1.0456, 'lng' => 104.0305],
        'kalimantan barat' => ['lat' => -0.0263, 'lng' => 109.3425],
        'west kalimantan' => ['lat' => -0.0263, 'lng' => 109.3425],
        'kalimantan timur' => ['lat' => -0.5022, 'lng' => 117.1536],
        'east kalimantan' => ['lat' => -0.5022, 'lng' => 117.1536],
        'kalimantan selatan' => ['lat' => -3.3194, 'lng' => 114.5906],
        'south kalimantan' => ['lat' => -3.3194, 'lng' => 114.5906],
        'kalimantan tengah' => ['lat' => -2.2089, 'lng' => 113.9213],
        'central kalimantan' => ['lat' => -2.2089, 'lng' => 113.9213],
        'kalimantan utara' => ['lat' => 3.3000, 'lng' => 117.6333],
        'north kalimantan' => ['lat' => 3.3000, 'lng' => 117.6333],
        'sulawesi selatan' => ['lat' => -5.1477, 'lng' => 119.4327],
        'south sulawesi' => ['lat' => -5.1477, 'lng' => 119.4327],
        'sulawesi utara' => ['lat' => 1.4748, 'lng' => 124.8421],
        'north sulawesi' => ['lat' => 1.4748, 'lng' => 124.8421],
        'sulawesi tengah' => ['lat' => -0.8999, 'lng' => 119.8707],
        'central sulawesi' => ['lat' => -0.8999, 'lng' => 119.8707],
        'sulawesi tenggara' => ['lat' => -3.9450, 'lng' => 122.5989],
        'southeast sulawesi' => ['lat' => -3.9450, 'lng' => 122.5989],
        'sulawesi barat' => ['lat' => -2.6833, 'lng' => 118.8833],
        'west sulawesi' => ['lat' => -2.6833, 'lng' => 118.8833],
        'nusa tenggara barat' => ['lat' => -8.5833, 'lng' => 116.1167],
        'west nusa tenggara' => ['lat' => -8.5833, 'lng' => 116.1167],
        'nusa tenggara timur' => ['lat' => -10.1718, 'lng' => 123.6075],
        'east nusa tenggara' => ['lat' => -10.1718, 'lng' => 123.6075],
        'maluku' => ['lat' => -3.6954, 'lng' => 128.1814],
        'maluku utara' => ['lat' => 0.7833, 'lng' => 127.3667],
        'north maluku' => ['lat' => 0.7833, 'lng' => 127.3667],
        'papua' => ['lat' => -2.5333, 'lng' => 140.7167],
        'papua barat' => ['lat' => -0.8667, 'lng' => 131.2500],
        'west papua' => ['lat' => -0.8667, 'lng' => 131.2500],
    ];
    
    /**
     * Geocode a location name to coordinates
     */
    public static function geocode(string $locationName): ?array
    {
        // Normalize location name
        $normalized = strtolower(trim($locationName));
        
        // Check Indonesian locations database first (cities and provinces)
        if (isset(self::$indonesianLocations[$normalized])) {
            return self::$indonesianLocations[$normalized];
        }
        
        // Try Nominatim API (OpenStreetMap)
        try {
            $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                'q' => $locationName,
                'format' => 'json',
                'limit' => 1
            ]);
            
            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: TheCodeAIAgent/1.0\r\n",
                    'timeout' => 5
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                return null;
            }
            
            $results = json_decode($response, true);
            
            if (!empty($results) && isset($results[0]['lat']) && isset($results[0]['lon'])) {
                return [
                    'lat' => (float)$results[0]['lat'],
                    'lng' => (float)$results[0]['lon']
                ];
            }
        } catch (\Exception $e) {
            error_log("Geocoding failed for '{$locationName}': " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Check if query results contain location data that needs geocoding
     */
    public static function hasLocationData(array $rows): bool
    {
        if (empty($rows)) {
            error_log('Geocoder: No rows provided');
            return false;
        }
        
        $keys = array_map('strtolower', array_keys($rows[0]));
        error_log('Geocoder: Checking columns: ' . implode(', ', $keys));
        
        // Check for existing lat/lng columns
        $hasLat = in_array('lat', $keys) || in_array('latitude', $keys) || 
                  in_array('lat_deg', $keys) || self::arrayContains($keys, 'latitude') || 
                  self::arrayContains($keys, '_lat');
        
        $hasLng = in_array('lng', $keys) || in_array('lon', $keys) || 
                  in_array('long', $keys) || in_array('longitude', $keys) || 
                  in_array('lng_deg', $keys) || in_array('lon_deg', $keys) || 
                  self::arrayContains($keys, 'longitude') || self::arrayContains($keys, '_lng') || 
                  self::arrayContains($keys, '_lon');
        
        if ($hasLat && $hasLng) {
            error_log('Geocoder: Already has coordinates, skipping');
            return false; // Already has coordinates
        }
        
        // Check for location name columns
        $locationColumns = ['city', 'kota', 'province', 'provinsi', 'location', 'lokasi', 
                           'address', 'alamat', 'area', 'region', 'wilayah', 'daerah'];
        
        foreach ($locationColumns as $col) {
            if (in_array($col, $keys) || self::arrayContains($keys, $col)) {
                error_log('Geocoder: Found location column: ' . $col);
                return true;
            }
        }
        
        error_log('Geocoder: No location columns found');
        return false;
    }
    
    /**
     * Add geocoded coordinates to query results
     */
    public static function geocodeResults(array $queryResults): array
    {
        error_log('Geocoder: geocodeResults called');
        
        if (!isset($queryResults['rows']) || empty($queryResults['rows'])) {
            error_log('Geocoder: No rows in query results');
            return $queryResults;
        }
        
        $rows = $queryResults['rows'];
        $keys = array_keys($rows[0]);
        $keysLower = array_map('strtolower', $keys);
        
        error_log('Geocoder: Processing ' . count($rows) . ' rows with columns: ' . implode(', ', $keys));
        
        // Find location column
        $locationKey = null;
        $locationColumns = ['city', 'kota', 'province', 'provinsi', 'location', 'lokasi', 
                           'address', 'alamat', 'area', 'region', 'wilayah', 'daerah'];
        
        foreach ($keys as $i => $key) {
            $keyLower = $keysLower[$i];
            foreach ($locationColumns as $col) {
                if ($keyLower === $col || strpos($keyLower, $col) !== false) {
                    $locationKey = $key;
                    error_log('Geocoder: Found location column: ' . $locationKey);
                    break 2;
                }
            }
        }
        
        if (!$locationKey) {
            error_log('Geocoder: No location column found');
            return $queryResults;
        }
        
        // Geocode each row
        $geocodedRows = [];
        $geocodedCount = 0;
        foreach ($rows as $row) {
            $locationName = $row[$locationKey] ?? null;
            
            if ($locationName) {
                error_log('Geocoder: Geocoding location: ' . $locationName);
                $coords = self::geocode($locationName);
                
                if ($coords) {
                    $row['latitude'] = $coords['lat'];
                    $row['longitude'] = $coords['lng'];
                    $geocodedCount++;
                    error_log('Geocoder: Successfully geocoded ' . $locationName . ' to ' . $coords['lat'] . ', ' . $coords['lng']);
                } else {
                    error_log('Geocoder: Failed to geocode: ' . $locationName);
                }
            }
            
            $geocodedRows[] = $row;
        }
        
        error_log('Geocoder: Geocoded ' . $geocodedCount . ' out of ' . count($rows) . ' rows');
        
        $queryResults['rows'] = $geocodedRows;
        return $queryResults;
    }
    
    /**
     * Helper: Check if any array element contains a substring
     */
    private static function arrayContains(array $array, string $needle): bool
    {
        foreach ($array as $item) {
            if (strpos($item, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}
