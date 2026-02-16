<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$index = isset($_GET['index']) ? intval($_GET['index']) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Santri - Website Tahfidz</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #121212; color: #fff; display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; }
        .container { max-width: 400px; width: 100%; padding: 20px; background: #1e1e1e; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.5); }
        h1 { text-align: center; color: #fff; font-size: 1.5em; margin-bottom: 20px; }
        form { display: flex; flex-direction: column; }
        label { margin: 10px 0 5px; color: #ccc; font-size: 0.9em; text-align: left; width: 100%; display: block; }
        input, select { padding: 10px; background: #333; color: #fff; border: 1px solid #444; border-radius: 5px; font-size: 1em; width: 100%; box-sizing: border-box; text-align: left; }
        input[type="number"] { -moz-appearance: textfield; }
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .group { margin: 15px 0; }
        .group h3 { text-align: center; color: #4CAF50; margin-bottom: 10px; font-size: 1.1em; }
        .sub-group { display: flex; justify-content: space-between; gap: 10px; }
        .sub-group div { width: 48%; }
        button { padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 5px; margin-top: 20px; cursor: pointer; font-size: 1em; width: 100%; }
        button:hover { background: #45a049; }
        .button-group { display: flex; justify-content: space-between; gap: 10px; }
        .logout { margin-top: 20px; text-align: center; font-size: 0.9em; }
        .logout a { color: #4CAF50; }
        #result { text-align: center; font-family: Arial, sans-serif; margin: 10px 0 5px; }
        #hafalanGroup { display: none; margin-top: 15px; }
        #pencapaianGroup { margin-top: 15px; }
        #hafalanAkhirGroup { display: block; }
        @media (max-width: 600px) { .sub-group { flex-direction: column; gap: 15px; } .sub-group div { width: 100%; } .button-group { flex-direction: column; } button { width: 100%; } }
    </style>
</head>
<body>
    <div class="container">
        <h1 id="santriName">Nama Santri</h1>
        <form id="editForm">
            <div class="group">
                <h3>Metode Binbaz</h3>
                <div class="sub-group">
                    <div>
                        <label for="jilid">Jilid:</label>
                        <select id="jilid" name="jilid">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="Tajwid Ghorib">Tajwid Ghorib</option>
                        </select>
                    </div>
                    <div>
                        <label for="halaman">Halaman:</label>
                        <input type="text" id="halaman" name="halaman">
                    </div>
                </div>
            </div>
            
            <div class="group">
                <label for="totalJuz">Total Hafalan Juz:</label>
                <select id="totalJuz" name="totalJuz" onchange="updateHalaman()">
                    <?php for ($i = 0; $i <= 30; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Juz</option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="group">
                <label for="totalHalaman">Total Hafalan Halaman:</label>
                <select id="totalHalaman" name="totalHalaman">
                    <?php for ($i = 0; $i <= 19; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Hal</option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div id="pencapaianGroup" class="group">
                <label for="pencapaian">Pencapaian Hafalan (Mingguan):</label>
                <select id="pencapaian" name="pencapaian">
                    <?php for ($i = 0; $i <= 19; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Hal</option>
                    <?php endfor; ?>
                    <option value="Persiapan Ujian">Persiapan Ujian</option>
                </select>
            </div>
            
            <div id="hafalanGroup" class="group">
                <label for="hafalanAwal">Hafalan Awal Pekan:</label>
                <select id="hafalanAwal" name="hafalanAwal">
                    <!-- Opsi akan diisi berdasarkan Juz -->
                </select>
                
                <div id="hafalanAkhirGroup">
                    <label for="hafalanAkhir">Hafalan Akhir Pekan:</label>
                    <select id="hafalanAkhir" name="hafalanAkhir">
                        <!-- Opsi akan diisi berdasarkan Juz -->
                    </select>
                </div>
            </div>
            
            <div class="group">
                <label for="tuhfa">Tuhfa (Bait):</label>
                <input type="text" id="tuhfa" name="tuhfa">
            </div>
            
            <button type="button" id="applyBtn">Terapkan</button>
            <div class="button-group">
                <button type="button" id="backBtn">Kembali ke Dashboard</button>
                <button type="button" id="nextBtn">Next</button>
            </div>
        </form>
        <div id="result"></div>
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <script>
        const index = <?php echo $index; ?>;
        const sheetName = '<?php echo isset($_SESSION['halaqoh']) ? $_SESSION['halaqoh'] : 'Sheet1'; ?>';
        let maxIndex = 0;
        let currentData = {};

        const surat1Juz = [
            'An-Naba\'', 'An-Nazi\'at', 'Abasa', 'At-Takwir', 'Al-Infitar', 'Al-Mutaffifin',
            'Al-Insyiqaq', 'Al-Buruj', 'At-Tariq', 'Al-A\'la', 'Al-Ghasyiyah', 'Al-Fajr',
            'Al-Balad', 'Asy-Syams', 'Al-Lail', 'Ad-Dhuha', 'Al-Insyirah', 'At-Tin',
            'Al-\'Alaq', 'Al-Qadr', 'Al-Bayyinah', 'Az-Zalzalah', 'Al-\'Adiyat',
            'Al-Qari\'ah', 'At-Takatsur', 'Al-\'Asr', 'Al-Humazah', 'Al-Fil', 'Quraisy',
            'Al-Ma\'un', 'Al-Kautsar', 'Al-Kafirun', 'An-Nasr', 'Al-Lahab', 'Al-Ikhlas',
            'Al-Falaq', 'An-Nas'
        ];
        const surat2Juz = [
            'Al-Mulk', 'Al-Qalam', 'Al-Haqqah', 'Al-Ma\'arij', 'Nuh', 'Al-Jinn', 
            'Al-Muzzammil', 'Al-Muddassir', 'Al-Qiyamah', 'Al-Insan', 'Al-Mursalat'
        ];

        window.addEventListener('load', function() {
            fetchData(index);
            fetchMaxIndex();
        });

        function fetchMaxIndex() {
            const formData = new FormData();
            formData.append('action', 'read');
            formData.append('range', sheetName + '!B3:B17');
            
            fetch('backend.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.values) {
                    maxIndex = data.values.length - 1;
                }
            });
        }

        function fetchData(idx) {
            const formData = new FormData();
            formData.append('action', 'read');
            formData.append('range', `${sheetName}!B${idx+3}:G${idx+3}`);
            
            fetch('backend.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('result').innerText = data.error;
                    return;
                }
                const row = data.values[0] || [];
                currentData = {
                    name: row[0] || 'Nama Santri',
                    jilid: row[1] || '1',
                    halaman: row[2] || '0',
                    hafalan: row[3] || '',
                    totalHafalan: row[4] || '',
                    tuhfa: row[5] || '0'
                };

                // Set form values
                document.getElementById('santriName').textContent = currentData.name;
                document.getElementById('jilid').value = currentData.jilid;
                document.getElementById('halaman').value = currentData.halaman;
                document.getElementById('tuhfa').value = currentData.tuhfa;

                // Parse Total Hafalan
                let totalJuz = '0', totalHalaman = '0';
                if (currentData.totalHafalan.includes('Juz')) {
                    const totalMatch = currentData.totalHafalan.match(/(\d+)\s*Juz\s*(\d*)\s*Hal?/);
                    if (totalMatch) {
                        totalJuz = totalMatch[1];
                        totalHalaman = totalMatch[2] || '0';
                    }
                } else {
                    const halMatch = currentData.totalHafalan.match(/(\d+)\s*Hal/);
                    if (halMatch) {
                        totalJuz = '0';
                        totalHalaman = halMatch[1];
                    }
                }
                document.getElementById('totalJuz').value = totalJuz;
                document.getElementById('totalHalaman').value = totalHalaman;
                updateHalaman();

                // Parse Hafalan/Pencapaian
                if (totalJuz === '0' || totalJuz === '1') {
                    if (currentData.hafalan.includes('Persiapan Ujian')) {
                        document.getElementById('hafalanAwal').value = 'Persiapan Ujian';
                        document.getElementById('hafalanAkhirGroup').style.display = 'none';
                    } else {
                        const hafalanMatch = currentData.hafalan.match(/(.+) - (.+)/);
                        if (hafalanMatch) {
                            document.getElementById('hafalanAwal').value = hafalanMatch[1] || '';
                            document.getElementById('hafalanAkhir').value = hafalanMatch[2] || '';
                            document.getElementById('hafalanAkhirGroup').style.display = 'block';
                        } else {
                            document.getElementById('hafalanAwal').value = currentData.hafalan || surat1Juz[0];
                            document.getElementById('hafalanAkhir').value = currentData.hafalan || surat1Juz[0];
                            document.getElementById('hafalanAkhirGroup').style.display = 'block';
                        }
                    }
                } else {
                    if (currentData.hafalan.includes('Persiapan Ujian')) {
                        document.getElementById('pencapaian').value = 'Persiapan Ujian';
                    } else {
                        const halMatch = currentData.hafalan.match(/(\d+)\s*Hal/);
                        document.getElementById('pencapaian').value = halMatch ? halMatch[1] : '1';
                    }
                }

                updateHafalanAndPencapaianGroup();
            })
            .catch(error => {
                document.getElementById('result').innerText = 'Error: ' + error;
            });
        }

        function updateHalaman() {
            const totalJuz = document.getElementById('totalJuz').value;
            const totalHalaman = document.getElementById('totalHalaman');
            const currentValue = totalHalaman.value;

            totalHalaman.innerHTML = '';

            if (totalJuz == 0) {
                for (let i = 1; i <= 22; i++) {
                    let option = document.createElement('option');
                    option.value = i;
                    option.text = i + ' Hal';
                    totalHalaman.appendChild(option);
                }
            } else {
                for (let i = 0; i <= 19; i++) {
                    let option = document.createElement('option');
                    option.value = i;
                    option.text = i + ' Hal';
                    totalHalaman.appendChild(option);
                }
            }

            totalHalaman.value = currentValue && totalHalaman.querySelector(`option[value="${currentValue}"]`) ? currentValue : (totalJuz == 0 ? '1' : '0');
        }

        function getCurrentJuz(totalJuz) {
            totalJuz = parseInt(totalJuz);
            if (totalJuz <= 5) {
                return 31 - totalJuz;
            } else {
                return totalJuz - 5;
            }
        }

        function updateHafalanAndPencapaianGroup() {
            const juz = document.getElementById('totalJuz').value;
            const hafalanGroup = document.getElementById('hafalanGroup');
            const pencapaianGroup = document.getElementById('pencapaianGroup');
            const awalSelect = document.getElementById('hafalanAwal');
            const akhirSelect = document.getElementById('hafalanAkhir');
            awalSelect.innerHTML = '';
            akhirSelect.innerHTML = '';

            let suratList = [];
            if (juz === '0') {
                suratList = surat1Juz;
            } else if (juz === '1') {
                suratList = surat2Juz;
            }

            if (suratList.length > 0) {
                hafalanGroup.style.display = 'block';
                pencapaianGroup.style.display = 'none';
                suratList.forEach(surat => {
                    const optionAwal = document.createElement('option');
                    optionAwal.value = surat;
                    optionAwal.textContent = surat;
                    awalSelect.appendChild(optionAwal);
                    
                    const optionAkhir = document.createElement('option');
                    optionAkhir.value = surat;
                    optionAkhir.textContent = surat;
                    akhirSelect.appendChild(optionAkhir);
                });
                const optionPersiapan = document.createElement('option');
                optionPersiapan.value = 'Persiapan Ujian';
                optionPersiapan.textContent = 'Persiapan Ujian';
                awalSelect.appendChild(optionPersiapan);

                awalSelect.value = currentData.hafalan.includes('Persiapan Ujian') ? 'Persiapan Ujian' : 
                    (currentData.hafalan.match(/(.+) - (.+)/) ? currentData.hafalan.split(' - ')[0] : currentData.hafalan || suratList[0]);
                akhirSelect.value = currentData.hafalan.includes('Persiapan Ujian') ? suratList[0] : 
                    (currentData.hafalan.match(/(.+) - (.+)/) ? currentData.hafalan.split(' - ')[1] : currentData.hafalan || suratList[0]);

                toggleHafalanAkhir();
            } else {
                hafalanGroup.style.display = 'none';
                pencapaianGroup.style.display = 'block';
                document.getElementById('pencapaian').value = currentData.hafalan.includes('Persiapan Ujian') ? 'Persiapan Ujian' : 
                    (currentData.hafalan.match(/(\d+)\s*Hal/) ? currentData.hafalan.match(/(\d+)\s*Hal/)[1] : '1');
            }
        }

        function toggleHafalanAkhir() {
            const awalValue = document.getElementById('hafalanAwal').value;
            const hafalanAkhirGroup = document.getElementById('hafalanAkhirGroup');
            if (awalValue === 'Persiapan Ujian') {
                hafalanAkhirGroup.style.display = 'none';
            } else {
                hafalanAkhirGroup.style.display = 'block';
            }
        }

        document.getElementById('totalJuz').addEventListener('change', () => {
            updateHalaman();
            updateHafalanAndPencapaianGroup();
        });

        document.getElementById('hafalanAwal').addEventListener('change', toggleHafalanAkhir);

        document.getElementById('applyBtn').addEventListener('click', applyChanges);

        function applyChanges() {
            let jilid = document.getElementById('jilid').value || currentData.jilid;
            let halamanInput = document.getElementById('halaman').value.trim();
            let halaman = halamanInput === '' ? currentData.halaman : (halamanInput === 'SELESAI' ? 'SELESAI' : parseInt(halamanInput) || 0);
            let totalJuz = parseInt(document.getElementById('totalJuz').value);
            let totalHalaman = parseInt(document.getElementById('totalHalaman').value) || 0;
            let tuhfaInput = document.getElementById('tuhfa').value.trim();
            let tuhfa = tuhfaInput === '' ? currentData.tuhfa : (tuhfaInput === 'SELESAI' ? 'SELESAI' : parseInt(tuhfaInput) || 0);
            let hafalanValue = '';

            let maxHal = 0;
            if (jilid === '1') maxHal = 41;
            else if (jilid === '2') maxHal = 40;
            else if (jilid === '3') maxHal = 47;
            else if (jilid === 'Tajwid Ghorib') maxHal = 41;

            if (typeof halaman === 'number' && halaman > maxHal) {
                halaman = 'SELESAI';
            }

            let tuhfaValue = (typeof tuhfa === 'string' && tuhfa === 'SELESAI') ? 'SELESAI' : (typeof tuhfa === 'number' && tuhfa > 60 ? 'SELESAI' : tuhfa);

            let totalHafalanValue = '';
            if (totalJuz === 0) {
                if (totalHalaman > 0) {
                    totalHafalanValue = `${totalHalaman} Hal`;
                }
            } else {
                totalHafalanValue = `${totalJuz} Juz`;
                if (totalHalaman > 0) {
                    totalHafalanValue += ` ${totalHalaman} Hal`;
                }
            }

            const currentJuz = getCurrentJuz(totalJuz);
            if (document.getElementById('hafalanGroup').style.display === 'block') {
                const awal = document.getElementById('hafalanAwal').value;
                if (awal === 'Persiapan Ujian') {
                    hafalanValue = `Persiapan Ujian Juz ${currentJuz}`;
                } else {
                    const akhir = document.getElementById('hafalanAkhir').value;
                    hafalanValue = (awal === akhir) ? awal : `${awal} - ${akhir}`;
                }
            } else {
                const pencapaian = document.getElementById('pencapaian').value;
                if (pencapaian === 'Persiapan Ujian') {
                    hafalanValue = `Persiapan Ujian Juz ${currentJuz}`;
                } else {
                    hafalanValue = `${pencapaian} Hal`;
                }
            }

            updateCell(`C${index+3}`, jilid);
            updateCell(`D${index+3}`, halaman);
            updateCell(`E${index+3}`, hafalanValue);
            updateCell(`F${index+3}`, totalHafalanValue);
            updateCell(`G${index+3}`, tuhfaValue);

            document.getElementById('result').innerText = 'Perubahan diterapkan!';
        }

        function updateCell(cell, value) {
            const range = `${cell}`;
            console.log('Updating cell:', `${sheetName}!${range}`, 'with value:', value);
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('cell', range);
            formData.append('value', value);
            
            fetch('backend.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('result').innerText = 'Error: ' + data.error;
                } else {
                    currentData = {
                        ...currentData,
                        jilid: document.getElementById('jilid').value || currentData.jilid,
                        halaman: document.getElementById('halaman').value.trim() === '' ? currentData.halaman : 
                            (document.getElementById('halaman').value.trim() === 'SELESAI' ? 'SELESAI' : parseInt(document.getElementById('halaman').value) || 0),
                        hafalan: document.getElementById('hafalanGroup').style.display === 'block' ? 
                            (document.getElementById('hafalanAwal').value === 'Persiapan Ujian' ? 
                                `Persiapan Ujian Juz ${getCurrentJuz(document.getElementById('totalJuz').value)}` : 
                                (document.getElementById('hafalanAwal').value === document.getElementById('hafalanAkhir').value ? 
                                    document.getElementById('hafalanAwal').value : 
                                    `${document.getElementById('hafalanAwal').value} - ${document.getElementById('hafalanAkhir').value}`)) : 
                            (document.getElementById('pencapaian').value === 'Persiapan Ujian' ? 
                                `Persiapan Ujian Juz ${getCurrentJuz(document.getElementById('totalJuz').value)}` : 
                                `${document.getElementById('pencapaian').value} Hal`),
                        totalHafalan: document.getElementById('totalJuz').value == 0 ? 
                            (document.getElementById('totalHalaman').value > 0 ? `${document.getElementById('totalHalaman').value} Hal` : '') : 
                            (document.getElementById('totalHalaman').value > 0 ? 
                                `${document.getElementById('totalJuz').value} Juz ${document.getElementById('totalHalaman').value} Hal` : 
                                `${document.getElementById('totalJuz').value} Juz`),
                        tuhfa: document.getElementById('tuhfa').value.trim() === '' ? currentData.tuhfa : 
                            (document.getElementById('tuhfa').value.trim() === 'SELESAI' ? 'SELESAI' : parseInt(document.getElementById('tuhfa').value) || 0)
                    };

                    let maxHal = 0;
                    if (currentData.jilid === '1') maxHal = 41;
                    else if (currentData.jilid === '2') maxHal = 40;
                    else if (currentData.jilid === '3') maxHal = 47;
                    else if (currentData.jilid === 'Tajwid Ghorib') maxHal = 41;

                    if (typeof currentData.halaman === 'number' && currentData.halaman > maxHal) {
                        currentData.halaman = 'SELESAI';
                    }
                    if (typeof currentData.tuhfa === 'number' && currentData.tuhfa > 60) {
                        currentData.tuhfa = 'SELESAI';
                    }
                }
            })
            .catch(error => {
                document.getElementById('result').innerText = 'Error: ' + error;
            });
        }

        document.getElementById('backBtn').addEventListener('click', () => {
            window.location.href = 'dashboard.php';
        });

        document.getElementById('nextBtn').addEventListener('click', () => {
            let nextIndex = index + 1;
            if (nextIndex > maxIndex) {
                window.location.href = 'dashboard.php';
            } else {
                window.location.href = `edit_santri.php?index=${nextIndex}`;
            }
        });
    </script>
</body>
</html>