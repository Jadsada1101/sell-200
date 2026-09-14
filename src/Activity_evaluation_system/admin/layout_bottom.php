            </main>

            <!-- Bottom Sub Footer -->
            <footer class="bg-white border-t border-gray-200 py-3 px-4 sm:px-6 text-xs text-gray-500 flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
                <div class="flex items-center gap-2">
                    <img src="../assets/images/logo.png" alt="RMUTL Logo" class="h-4 w-auto object-contain">
                    <span>มหาวิทยาลัยเทคโนโลยีราชมงคลล้านนา</span>
                </div>
                <div class="text-gray-400 font-light">
                    ระบบประเมินการจัดกิจกรรม
                </div>
            </footer>
        </div>
    </div>

    <!-- Script ควบคุมการเปิด-ปิดเมนูบนหน้าจอมือถือ (Mobile Drawer Sidebar) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const openBtn = document.getElementById('sidebar-toggle-btn');
            const closeBtn = document.getElementById('sidebar-close-btn');

            function openSidebar() {
                if (!sidebar || !overlay) return;
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeSidebar() {
                if (!sidebar || !overlay) return;
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            if (openBtn) openBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (overlay) overlay.addEventListener('click', closeSidebar);

            // ปิดเมนูเมื่อกดปุ่ม Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeSidebar();
            });

            // คืนค่า scroll เมื่อหน้าจอถูกปรับขนาดเป็นหน้าจอคอม
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    closeSidebar();
                }
            });
        });
    </script>
</body>
</html>
