document.addEventListener('DOMContentLoaded', () => {
    
    // --- İLÇE YÜKLEME SİSTEMİ (Ajax) ---
    const citySelect = document.getElementById('citySelect');
    const districtSelect = document.getElementById('districtSelect');

    if (citySelect && districtSelect) {
        citySelect.addEventListener('change', function() {
            const cityId = this.value;

            // Eğer il seçimi iptal edilirse ilçeyi sıfırla
            if (!cityId) {
                districtSelect.innerHTML = '<option value="">İlçe Seçiniz</option>';
                districtSelect.disabled = true;
                return;
            }

            // İlçeleri Yükle
            loadDistricts(cityId);
        });
    }

    function loadDistricts(cityId) {
        districtSelect.disabled = true;
        districtSelect.innerHTML = '<option value="">Yükleniyor...</option>';

        // ajax_districts.php dosyasına istek at
        fetch(`ajax_districts.php?city_id=${cityId}`)
            .then(response => response.json())
            .then(data => {
                let options = '<option value="">İlçe Seçiniz</option>';
                data.forEach(dist => {
                    options += `<option value="${dist.id}">${dist.title}</option>`;
                });
                districtSelect.innerHTML = options;
                districtSelect.disabled = false;
            })
            .catch(error => {
                console.error('Hata:', error);
                districtSelect.innerHTML = '<option value="">Hata oluştu</option>';
            });
    }

});

// --- SIDEBAR AKORDİYON MENÜ (Global Fonksiyon) ---
// HTML'deki onclick="toggleSubMenu(this)" bunu çağırır.
function toggleSubMenu(button) {
    // 1. Butonun içinde bulunduğu ana maddeyi (li) bul
    const categoryItem = button.closest('.cat-item');
    
    // 2. Alt menüyü (ul) bul
    const subMenu = categoryItem.querySelector('.sub-menu');
    
    // 3. Menü varsa işlemi yap
    if (subMenu) {
        // 'open' class'ı ekle/çıkar (CSS ile görünür olur)
        subMenu.classList.toggle('open');
        
        // Ok işaretini döndürmek için 'active' class'ı ekle/çıkar
        categoryItem.classList.toggle('active');
    }
}