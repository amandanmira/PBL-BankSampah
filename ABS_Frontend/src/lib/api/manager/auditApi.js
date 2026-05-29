import axios from 'axios';

export const getListGudang = async () => {
  return await axios.get('/api/laporan/list-gudang');
};

export const getListSampah = async () => {
  return await axios.get('/api/laporan/list-sampah');
};

// Loop through pagination to fetch all history
export const fetchAllRiwayatPenjemputan = async (onProgress) => {
  let allData = [];
  let currentPage = 1;
  let lastPage = 1;

  do {
    const response = await axios.get(`/api/manager/riwayat-penjemputan?page=${currentPage}`);
    const data = response.data;

    allData = allData.concat(data.data || data.penjemputan?.data || []);
    lastPage = data.last_page || data.penjemputan?.last_page || 1;

    if (onProgress) {
      onProgress(Math.round((currentPage / lastPage) * 100));
    }

    currentPage++;
  } while (currentPage <= lastPage);

  return allData;
};

// Loop through pagination to fetch all history
export const fetchAllRiwayatPenarikan = async (onProgress) => {
  let allData = [];
  let currentPage = 1;
  let lastPage = 1;

  do {
    const response = await axios.get(`/api/manager/riwayat-penarikan?page=${currentPage}`);
    const data = response.data;

    // riwayat-penarikan returns { penarikan: { data: [...], last_page: x } }
    allData = allData.concat(data.penarikan?.data || []);
    lastPage = data.penarikan?.last_page || 1;

    if (onProgress) {
      onProgress(Math.round((currentPage / lastPage) * 100));
    }

    currentPage++;
  } while (currentPage <= lastPage);

  return allData;
};

export const exportLaporanExcel = async (params) => {
  return await axios.get('/api/cetak-laporan/excel', {
    params,
    responseType: 'blob'
  });
};

export const exportLaporanPdf = async (params) => {
  return await axios.get('/api/cetak-laporan/pdf', {
    params
  });
};
