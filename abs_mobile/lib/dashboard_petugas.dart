import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'services/api_service.dart'; // Import API Service

class DashboardPetugas extends StatefulWidget {
  final String token; // Menerima token dari halaman Login
  
  const DashboardPetugas({super.key, required this.token});

  @override
  State<DashboardPetugas> createState() => _DashboardPetugasState();
}

class _DashboardPetugasState extends State<DashboardPetugas> {
  int _selectedIndex = 0;
  late ScrollController _scrollController;
  bool _isBottomBarVisible = true;
  bool _isLoading = true; // State loading

  final ApiService _apiService = ApiService();

  // Variabel penampung data API
  List<dynamic> _perluPerhatian = [];
  List<dynamic> _aktivitasTerkini = [];
  
  // Variabel statistik (dihitung dari data)
  int _totalRequest = 0;
  int _totalTransaksi = 0;

  final Color bgColor = const Color(0xFFF9FAF5);
  final Color primaryGreen = const Color(0xFF4A6B46);
  final Color lightGreen = const Color(0xFFDFEAD9);
  final Color purpleCard = const Color(0xFF8A5B73);
  final Color grayCard = const Color(0xFFE8E9E4);

  @override
  void initState() {
    super.initState();
    _scrollController = ScrollController();
    
    _scrollController.addListener(() {
      if (_scrollController.position.userScrollDirection == ScrollDirection.reverse) {
        if (_isBottomBarVisible) setState(() => _isBottomBarVisible = false);
      }
      if (_scrollController.position.userScrollDirection == ScrollDirection.forward) {
        if (!_isBottomBarVisible) setState(() => _isBottomBarVisible = true);
      }
    });

    // PANGGIL FUNGSI AMBIL DATA SAAT HALAMAN DIBUKA
    _loadDashboardData();
  }

  Future<void> _loadDashboardData() async {
    setState(() => _isLoading = true);

    // Ambil ketiga data secara bersamaan
    final penjemputan = await _apiService.getPenjemputan(widget.token);
    final riwayatJemput = await _apiService.getRiwayatPenjemputan(widget.token);
    final riwayatTarik = await _apiService.getRiwayatPenarikan(widget.token);

    // Filter "Yang Perlu Perhatian" (Hanya pending/proses)
    List<dynamic> perhatian = penjemputan.where((p) => p['status'] == 'pending' || p['status'] == 'proses').toList();

    // Gabungkan "Aktivitas Terkini" (Riwayat Jemput & Riwayat Tarik)
    List<dynamic> aktivitas = [];
    for (var item in riwayatJemput) {
      item['tipe_aktivitas'] = 'Penjemputan';
      aktivitas.add(item);
    }
    for (var item in riwayatTarik) {
      item['tipe_aktivitas'] = 'Penarikan';
      aktivitas.add(item);
    }
    
    // Hitung Statistik Sementara (Jika belum ada endpoint /dashboard khusus)
    int reqCount = perhatian.length;
    int transCount = aktivitas.length;

    setState(() {
      _perluPerhatian = perhatian;
      _aktivitasTerkini = aktivitas;
      _totalRequest = reqCount;
      _totalTransaksi = transCount;
      _isLoading = false;
    });
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: bgColor,
      extendBody: true, 
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            _buildCustomTopBar(),
            Expanded(
              child: _isLoading 
                ? const Center(child: CircularProgressIndicator(color: Color(0xFF4A6B46))) // Animasi Loading
                : SingleChildScrollView(
                controller: _scrollController,
                padding: const EdgeInsets.only(left: 20.0, right: 20.0, top: 10.0, bottom: 120.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Dashboard Petugas', style: TextStyle(color: Colors.grey[600], fontSize: 14)),
                    const SizedBox(height: 4),
                    const Text('Selamat Datang!', style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, height: 1.2)),
                    const SizedBox(height: 24),

                    // KARTU STATISTIK
                    Row(
                      children: [
                        Expanded(child: _buildStatCard(color: primaryGreen, icon: Icons.recycling, value: '$_totalRequest', label: 'Tugas Aktif', textColor: Colors.white)),
                        const SizedBox(width: 12),
                        Expanded(child: _buildStatCard(color: lightGreen, icon: Icons.local_shipping, value: '8', label: 'Pickup Requests', textColor: primaryGreen)),
                      ],
                    ),
                    const SizedBox(height: 12),
                    _buildFullWidthCard(color: purpleCard, icon: Icons.receipt_long, value: '$_totalTransaksi', label: 'Total Riwayat', textColor: Colors.white),
                    const SizedBox(height: 12),
                    _buildFullWidthCard(color: grayCard, icon: Icons.scale, value: '0', unit: 'kg', label: 'Total Waste (Belum API)', textColor: Colors.black87),
                    const SizedBox(height: 32),

                    // LIST: YANG PERLU PERHATIAN
                    const Text('Yang Perlu Perhatian', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 16),
                    if (_perluPerhatian.isEmpty) 
                      const Text('Tidak ada tugas pending.', style: TextStyle(fontStyle: FontStyle.italic)),
                    ..._perluPerhatian.map((item) {
                      bool isPending = item['status'] == 'pending';
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 12.0),
                        child: _buildAttentionCard(
                          icon: isPending ? Icons.hourglass_empty : Icons.directions_run,
                          title: 'Jemput #${item['penjemputan_id'] ?? '-'}',
                          subtitle: item['alamat'] ?? 'Tanpa alamat',
                          badgeText: (item['status'] ?? '').toString().toUpperCase(),
                          badgeColor: isPending ? const Color(0xFFFFF3CD) : const Color(0xFFD6E8D0),
                          badgeTextColor: isPending ? const Color(0xFF856404) : primaryGreen,
                        ),
                      );
                    }),
                    const SizedBox(height: 32),

                    // LIST: AKTIVITAS TERKINI
                    const Text('Aktivitas Terkini', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 16),
                    if (_aktivitasTerkini.isEmpty) 
                      const Text('Belum ada aktivitas.', style: TextStyle(fontStyle: FontStyle.italic)),
                    ..._aktivitasTerkini.asMap().entries.map((entry) {
                      int idx = entry.key;
                      var item = entry.value;
                      bool isLast = idx == _aktivitasTerkini.length - 1;
                      
                      String title = '';
                      if (item['tipe_aktivitas'] == 'Penjemputan') {
                        title = 'Jemput ${item['status']} - #${item['penjemputan_id']}';
                      } else {
                        title = 'Tarik ${item['status']} - Rp${item['jumlah'] ?? 0}';
                      }

                      return _buildTimelineItem(
                        title: title,
                        time: item['created_at'] ?? 'Baru saja', // Sesuaikan jika ada format tanggal
                        isActive: item['status'] == 'selesai',
                        isLast: isLast,
                      );
                    }),
                    
                    const SizedBox(height: 50),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: AnimatedSlide(
        duration: const Duration(milliseconds: 300),
        offset: _isBottomBarVisible ? Offset.zero : const Offset(0, 1.5),
        child: _buildCustomBottomBar(),
      ),
    );
  }

  // ... (SISA KODE WIDGET _buildCustomTopBar, _buildCustomBottomBar, _buildNavItem, _buildStatCard, _buildFullWidthCard, _buildAttentionCard, _buildTimelineItem SAMA PERSIS SEPERTI SEBELUMNYA) ...

  Widget _buildCustomTopBar() {
    return Container(
      margin: const EdgeInsets.fromLTRB(20, 10, 20, 10),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(50), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 15, offset: const Offset(0, 5))]),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              CircleAvatar(backgroundColor: primaryGreen, radius: 18, child: const Text('BP', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold))),
              const SizedBox(width: 12),
              const Text('Bank Sampah', style: TextStyle(color: Color(0xFF334A2E), fontWeight: FontWeight.bold, fontSize: 16)),
            ],
          ),
          IconButton(icon: const Icon(Icons.notifications_none_rounded, color: Colors.grey), onPressed: () {}, constraints: const BoxConstraints(), padding: const EdgeInsets.only(right: 8)),
        ],
      ),
    );
  }

  Widget _buildCustomBottomBar() {
    return Container(
      margin: const EdgeInsets.only(left: 30, right: 30, bottom: 24),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(color: const Color(0xFF3A5B32), borderRadius: BorderRadius.circular(50), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.2), blurRadius: 20, offset: const Offset(0, 10))]),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
        children: [
          _buildNavItem(0, Icons.dashboard), 
          _buildNavItem(1, Icons.recycling),
          _buildNavItem(2, Icons.assignment_outlined),
          _buildNavItem(3, Icons.person_outline),
        ],
      ),
    );
  }

  Widget _buildNavItem(int index, IconData icon) {
    bool isSelected = _selectedIndex == index;
    return GestureDetector(
      onTap: () => setState(() => _selectedIndex = index),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeOutCubic,
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(color: isSelected ? Colors.white.withOpacity(0.15) : Colors.transparent, shape: BoxShape.circle),
        child: Icon(icon, color: isSelected ? Colors.white : Colors.white.withOpacity(0.5), size: 26),
      ),
    );
  }

  Widget _buildStatCard({required Color color, required IconData icon, required String value, required String label, required Color textColor}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(16)),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Icon(icon, color: textColor.withOpacity(0.7)), const SizedBox(height: 30), Text(value, style: TextStyle(color: textColor, fontSize: 28, fontWeight: FontWeight.bold)), Text(label, style: TextStyle(color: textColor.withOpacity(0.9), fontSize: 13))]),
    );
  }

  Widget _buildFullWidthCard({required Color color, required IconData icon, required String value, required String label, required Color textColor, String unit = ''}) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(16)),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Icon(icon, color: textColor.withOpacity(0.7)), const SizedBox(height: 8), Text(label, style: TextStyle(color: textColor.withOpacity(0.9), fontSize: 14))]),
          Row(crossAxisAlignment: CrossAxisAlignment.baseline, textBaseline: TextBaseline.alphabetic, children: [Text(value, style: TextStyle(color: textColor, fontSize: 36, fontWeight: FontWeight.bold)), if (unit.isNotEmpty) Text(' $unit', style: TextStyle(color: textColor, fontSize: 20))]),
        ],
      ),
    );
  }

  Widget _buildAttentionCard({required IconData icon, required String title, required String subtitle, required String badgeText, required Color badgeColor, required Color badgeTextColor}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.withOpacity(0.2))),
      child: Row(
        children: [
          CircleAvatar(backgroundColor: const Color(0xFFF0F0F0), child: Icon(icon, color: Colors.black87, size: 20)),
          const SizedBox(width: 16),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)), Text(subtitle, style: TextStyle(color: Colors.grey[600], fontSize: 13), maxLines: 1, overflow: TextOverflow.ellipsis)])),
          Container(padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6), decoration: BoxDecoration(color: badgeColor, borderRadius: BorderRadius.circular(20)), child: Text(badgeText, style: TextStyle(color: badgeTextColor, fontSize: 10, fontWeight: FontWeight.bold))),
        ],
      ),
    );
  }

  Widget _buildTimelineItem({required String title, required String time, bool isActive = false, bool isLast = false}) {
    return IntrinsicHeight(
      child: Row(
        children: [
          SizedBox(width: 20, child: Column(children: [Container(width: 12, height: 12, decoration: BoxDecoration(color: isActive ? primaryGreen : Colors.grey[300], shape: BoxShape.circle)), if (!isLast) Expanded(child: Container(width: 2, color: Colors.grey[200]))])),
          const SizedBox(width: 16),
          Expanded(child: Padding(padding: const EdgeInsets.only(bottom: 20.0), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(title, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15)), const SizedBox(height: 4), Text(time, style: TextStyle(color: Colors.grey[500], fontSize: 13))]))),
        ],
      ),
    );
  }
} 