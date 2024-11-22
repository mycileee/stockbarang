<?php 
session_start();
$conn = mysqli_connect("localhost", "root", "topabis", "stockbarang");


// Menambah barang baru
if(isset($_POST['addnewbarang'])){
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $stock = $_POST['stock'];

    // Type gambar
    $allowed_extension = array('png','jpg');
    $nama = $_FILES['file']['name']; //ambil nama gambar
    $dot = explode('.',$nama);
    $ekstensi = strtolower(end($dot)); //ambil ekstensinya
    $ukuran = $_FILES['file']['size']; //ambil ukuran gambar
    $file_tmp = $_FILES['file']['tmp_name']; //ambil lokasi gambarnya

    // penamaan file > ekstensi
    $image = md5(uniqid($nama,true) . time()).'.'.$ekstensi; //menggabungkan nama file yang dienkripsi dngn ekstensinya

    // Validasi data
    $cek = mysqli_query($conn, "select * from stock where namabarang='$namabarang'");
    $hitung = mysqli_num_rows($cek);

    if($hitung<1){
    // Jika Belum Ada

        // proses upload gambar
        if(in_array($ekstensi, $allowed_extension) === true){
            // validasi ukuran filenya
            if($ukuran < 15000000){
                move_uploaded_file($file_tmp, 'images/'.$image);

                $addtotable = mysqli_query($conn, "insert into stock (namabarang, deskripsi, stock, image) values('$namabarang','$deskripsi','$stock', '$image')");
                if($addtotable){
                    header('location:index.php');
                } else {
                    echo 'gagal';
                    header('location:index.php');
                }
            } else {
                // kalau file nya lebih dari 15mb
                echo '
                <script>
                    alert("Ukuran terlau besar";
                    windows.location.href="index.php";
                </script>
                ';
            }
        }  else {
            // kalau filenya bukan jpg / png
            echo '
            <script>
                alert("Jenis File tidak cocok";
                windows.location.href="index.php";
            </script>
            ';
        } 
        
    } else {
        // Jika sudah ada
        echo '
        <script>
            alert("Nama barang sudah terdaftar";
            windows.location.href="index.php";
        </script>
        ';
    }
};

// Menambah barang masuk
if(isset($_POST['addbarangmasuk'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $cekstocksekarang = mysqli_query($conn, "select * from stock where idbarang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstocksekarang);

    $stocksekarang = $ambildatanya['stock'];
    $tambahkanstocksekarangdenganquantity = $stocksekarang+$qty;

    $addtomasuk = mysqli_query($conn, "insert into masuk (idbarang, penerima, qty) values('$barangnya','$penerima','$qty')");
    $updatestockmasuk = mysqli_query($conn, "update stock set stock='$tambahkanstocksekarangdenganquantity' where idbarang='$barangnya'");
    if($addtomasuk&&$updatestockmasuk){
        header('location:masuk.php');
    } else {
        echo 'gagal';
        header('location:masuk.php');
    }

}

// Menambah barang keluar
if(isset($_POST['addbarangkeluar'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $cekstocksekarang = mysqli_query($conn, "select * from stock where idbarang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstocksekarang);

    $stocksekarang = $ambildatanya['stock'];

    if($stocksekarang >= $qty){
        // Jika barangnya cukup
        $tambahkanstocksekarangdenganquantity = $stocksekarang-$qty;

        $addtokeluar = mysqli_query($conn, "insert into keluar (idbarang, penerima, qty) values('$barangnya','$penerima','$qty')");
        $updatestockmasuk = mysqli_query($conn, "update stock set stock='$tambahkanstocksekarangdenganquantity' where idbarang='$barangnya'");
        if($addtokeluar&&$updatestockmasuk){
            header('location:keluar.php');
        } else {
            echo 'gagal';
            header('location:keluar.php');
        }
    } else {
        // Jika barang tidak cukup
        echo '
        <script>
            alert("Stock saat ini tidak cukup");
            windos.localtion.href="keluar.php";
        </script>
        ';
    }
}

// Update Info Barang

if(isset($_POST['updatebarang'])){
    $idb = $_POST['idb'];
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];

    $update = mysqli_query($conn, "update stock set namabarang='$namabarang', deskripsi='$deskripsi' where idbarang ='$idb' ");
    if($update){
        header('location:index.php');
    } else {
        echo 'gagal';
        header('location:index.php');
    }
}

// Hapus Barang

if(isset($_POST['hapusbarang'])){
    $idb = $_POST['idb'];
    
    $hapus = mysqli_query($conn, "delete from stock where idbarang='$idb'");
    if($hapus){
        header('location:index.php');
    } else {
        echo 'gagal';
        header('location:index.php');
    }
}

// Mengubah Data Barang Masuk

if(isset($_POST['updatebarangmasuk'])){
    $idb = $_POST['idb'];  
    $idm = $_POST['idm'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $lihatstock = mysqli_query($conn, "select * from stock where idbarang='$idb'");
    $stocknya = mysqli_fetch_array($lihatstock);
    $stockskrg = $stocknya['stock'];

    $qtyskrg = mysqli_query($conn, "select * from masuk where idmasuk='$idm'");
    $qtynya = mysqli_fetch_array($qtyskrg);
    $qtyskrg = $qtynya['qty'];

    if($qty>$qtyskrg){
        $selisih = $qty-$qtyskrg;
        $kurangin = $stockskrg + $selisih; 
        $kurangistocknya = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update masuk set qty='$qty', penerima='$penerima' where idmasuk='$idm'");
            if($kurangistocknya&&$updatenya){
                header('location:masuk.php');
            } else {
                echo 'gagal';
                header('location:masuk.php');
            }

    }   else {
        $selisih = $qtyskrg-$qty;
        $kurangin = $stockskrg - $selisih; 
        $kurangistocknya = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update masuk set qty='$qty', penerima='$penerima' where idmasuk='$idm'");
            if($kurangistocknya&&$updatenya){
                header('location:masuk.php');
            } else {
                echo 'gagal';
                header('location:masuk.php');
            }

    }

}

// Menghapus Barang Masuk
if(isset($_POST['hapusbarangmasuk'])){
    $idb = $_POST['idb'];
    $qty = $_POST['kty'];
    $idm = $_POST['idm'];

    $getdatastock = mysqli_query($conn, "select * from stock where idbarang='$idb'");
    $data = mysqli_fetch_array($getdatastock);
    $stock = $data['stock'];

    $selisih = $stock-$qty;

    $update = mysqli_query($conn, "update stock set stock='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "delete from masuk where idmasuk='$idm'");

    if($update&&$hapusdata){
        header('location:masuk.php');
    } else {
        header('location:masuk.php');
    }

}

// Mengubah Data Barang Keluar

if(isset($_POST['updatebarangkeluar'])){
    $idb = $_POST['idb'];  
    $idk = $_POST['idk'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $lihatstock = mysqli_query($conn, "select * from stock where idbarang='$idb'");
    $stocknya = mysqli_fetch_array($lihatstock);
    $stockskrg = $stocknya['stock'];

    $qtyskrg = mysqli_query($conn, "select * from keluar where idkeluar='$idk'");
    $qtynya = mysqli_fetch_array($qtyskrg);
    $qtyskrg = $qtynya['qty'];

    if($qty>$qtyskrg){
        $selisih = $qty-$qtyskrg;
        $kurangin = $stockskrg - $selisih; 
        $kurangistocknya = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update keluar set qty='$qty', penerima='$penerima' where idkeluar='$idk'");
            if($kurangistocknya&&$updatenya){
                header('location:keluar.php');
            } else {
                echo 'gagal';
                header('location:keluar.php');
            }

    }   else {
        $selisih = $qtyskrg-$qty;
        $kurangin = $stockskrg + $selisih; 
        $kurangistocknya = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update keluar set qty='$qty', penerima='$penerima' where idkeluar='$idk'");
            if($kurangistocknya&&$updatenya){
                header('location:keluar.php');
            } else {
                echo 'gagal';
                header('location:keluar.php');
            }

    }

}

// Menghapus Barang Keluar
if(isset($_POST['hapusbarangkeluar'])){
    $idb = $_POST['idb'];
    $qty = $_POST['kty'];
    $idk = $_POST['idk'];

    $getdatastock = mysqli_query($conn, "select * from stock where idbarang='$idb'");
    $data = mysqli_fetch_array($getdatastock);
    $stock = $data['stock'];

    $selisih = $stock+$qty;

    $update = mysqli_query($conn, "update stock set stock='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "delete from keluar where idkeluar='$idk'");

    if($update&&$hapusdata){
        header('location:keluar.php');
    } else {
        header('location:keluar.php');
    }

}

?>