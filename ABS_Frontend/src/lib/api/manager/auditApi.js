import axios from 'axios';

export const getListGudang = async () => {
  return await axios.get('/api/laporan/list-gudang');
};

export const getListSampah = async () => {
  return await axios.get('/api/laporan/list-sampah');
};

export const getAuditData = async (page = 1, filters = {}, perPage = 10) => {
  const params = {
    page,
    per_page: perPage,
    gudang: filters.gudang,
    durasi: filters.durasi,
    jenisSampah: filters.jenisSampah?.join(','),
    search: filters.search
  };
  return await axios.get('/api/manager/audit-data', { params });
};

export const getAuditSummary = async (filters = {}) => {
  const params = {
    gudang: filters.gudang,
    durasi: filters.durasi,
    jenisSampah: filters.jenisSampah?.join(','),
    search: filters.search
  };
  return await axios.get('/api/manager/audit-summary', { params });
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
