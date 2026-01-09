<?php
session_start();
include 'config/koneksi.php';

// Cek Login
if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){ 
    header("location:index.php"); 
    exit; 
}

// AMBIL DATA DARI FORMULIR SEBELUMNYA
$semester = isset($_GET['semester']) ? $_GET['semester'] : '1'; // Default Semester 1
$kelas    = isset($_GET['kelas']) ? $_GET['kelas'] : '11';       // Default Kelas 11
$mapel    = isset($_GET['mapel']) ? $_GET['mapel'] : 'pwpb';     // (Sementara hanya hiasan karena belum ada tabel mapel)
$urutan   = isset($_GET['urutan']) ? $_GET['urutan'] : '';       // Filter pertemuan spesifik

$id_siswa = $_SESSION['id_siswa'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Materi - Kelas <?php echo $kelas; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: { extend: { colors: { schoolOrange: '#f59e0b', schoolBlue: '#1e3a8a' } } }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

    <nav class="bg-schoolOrange text-white shadow-md sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="semester.php" class="font-bold hover:text-blue-900 transition flex items-center gap-2">
                <i class="fas fa-filter"></i> Ganti Filter
            </a>
            <div class="flex gap-2">
                <span class="bg-white/20 px-3 py-1 rounded-lg text-sm font-bold shadow-sm">
                    Kelas <?php echo $kelas; ?>
                </span>
                <span class="bg-white/20 px-3 py-1 rounded-lg text-sm font-bold shadow-sm">
                    Sem. <?php echo $semester; ?>
                </span>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 py-8 w-full flex-grow">
        
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-schoolBlue">Materi Pembelajaran</h1>
            <p class="text-gray-500 mt-2">
                Menampilkan materi untuk <b>Kelas <?php echo $kelas; ?></b> - <b>Semester <?php echo $semester; ?></b>
                <?php if($urutan != '') echo "<br><span class='text-schoolOrange font-bold'>(Filter: Pertemuan $urutan)</span>"; ?>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php
            // SUSUN QUERY SQL BERDASARKAN FILTER
            $sql = "SELECT * FROM materi WHERE semester='$semester' AND kelas='$kelas'";
            
            // Jika user memilih pertemuan spesifik, tambahkan filter urutan
            if($urutan != ''){
                $sql .= " AND urutan='$urutan'";
            }
            
            $sql .= " ORDER BY urutan ASC"; // Urutkan dari pertemuan 1, 2, dst
            
            $query = mysqli_query($conn, $sql);
            
            // Jika data kosong
            if(mysqli_num_rows($query) == 0){
                echo "
                <div class='col-span-1 md:col-span-2 text-center py-10 bg-white rounded-xl border-2 border-dashed border-gray-300'>
                    <i class='fas fa-folder-open text-4xl text-gray-300 mb-3'></i>
                    <p class='text-gray-500'>Belum ada materi untuk filter ini.</p>
                    <a href='semester.php' class='text-schoolOrange font-bold hover:underline mt-2 inline-block'>Coba filter lain</a>
                </div>";
            }
            
            while($m = mysqli_fetch_array($query)){
                $id_materi = $m['id_materi'];
                
                // Cek Progress
                $q_prog = mysqli_query($conn, "SELECT * FROM progress WHERE id_siswa='$id_siswa' AND id_materi='$id_materi'");
                $prog = mysqli_fetch_array($q_prog);
                
                // Logika Status
                $status = "buka";
                $nilai_tombol = "bg-schoolBlue hover:bg-blue-900";
                $link = "materi_belajar.php?id=".$id_materi;
                $icon = "fa-play-circle";
                $text_btn = "Mulai Belajar";
                
                if(isset($prog['status_materi']) && $prog['status_materi'] == 'lulus'){
                    $nilai_tombol = "bg-green-600 hover:bg-green-700";
                    $text_btn = "Sudah Lulus";
                    $icon = "fa-check-circle";
                }
            ?>

            <div class="bg-white rounded-xl shadow-md border border-gray-100 hover:shadow-xl transition duration-300 overflow-hidden flex flex-col group">
                <div class="p-6 flex-grow relative">
                    <div class="absolute top-4 right-4 opacity-10 text-6xl font-black text-gray-300 group-hover:text-schoolOrange transition">
                        <?php echo $m['urutan']; ?>
                    </div>

                    <div class="flex items-center gap-2 mb-4">
                        <span class="bg-orange-100 text-schoolOrange text-xs font-bold px-3 py-1 rounded-full">
                            PERTEMUAN <?php echo $m['urutan']; ?>
                        </span>
                        <?php if(isset($prog['status_materi']) && $prog['status_materi'] == 'lulus') { ?>
                            <span class="text-green-600 text-xs font-bold flex items-center gap-1">
                                <i class="fas fa-check"></i> Selesai
                            </span>
                        <?php } ?>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-800 mb-2 leading-snug relative z-10">
                        <?php echo $m['judul_materi']; ?>
                    </h3>
                    
                    <p class="text-gray-500 text-sm line-clamp-2 relative z-10">
                        <?php echo strip_tags(substr($m['deskripsi'], 0, 100)) . '...'; ?>
                    </p>
                </div>

                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                    <a href="<?php echo $link; ?>" class="<?php echo $nilai_tombol; ?> text-white w-full py-3 rounded-lg font-bold shadow transition flex items-center justify-center gap-2">
                        <i class="fas <?php echo $icon; ?>"></i> <?php echo $text_btn; ?>
                    </a>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

</body>
</html>