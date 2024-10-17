const express = require('express');
const path = require('path');
const app = express();
const port = 1710;

// Menyajikan file statis dari folder 'Public'
app.use(express.static(path.join(__dirname, 'Public')));

// Rute untuk halaman utama
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'Public', 'Client', 'Welcome.html'));
});

// Rute tambahan untuk menangani file di dalam folder Client
app.use('/client', express.static(path.join(__dirname, 'Public', 'Client')));

app.listen(port, () => {
    console.log(`Server berjalan di http://localhost:1710`);
});