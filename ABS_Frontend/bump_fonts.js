const fs = require('fs');

const files = [
  'src/pages/dashboards/pengepul/BeliSampah.vue',
  'src/pages/dashboards/pengepul/DetailPesanan.vue',
  'src/pages/dashboards/pengepul/RingkasanPembelian.vue'
];

const fontMap = {
  'text-\\[8px\\]': 'text-[11px]',
  'text-\\[9px\\]': 'text-xs',
  'text-\\[10px\\]': 'text-xs',
  'text-\\[11px\\]': 'text-sm',
  'text-\\[12px\\]': 'text-sm',
  'text-xs': 'text-sm',
  'text-sm': 'text-base',
  'text-base': 'text-lg',
  'text-lg': 'text-xl',
  'text-xl': 'text-2xl',
  'text-2xl': 'text-3xl',
  'text-3xl': 'text-4xl'
};

files.forEach(file => {
  const path = '/home/soulhunter/Storage/Codes/ABS/PBL-BankSampah/ABS_Frontend/' + file;
  if (fs.existsSync(path)) {
    let content = fs.readFileSync(path, 'utf8');
    
    // We need a regex that matches these exactly as whole words. 
    // Note that hyphen and bracket are part of the word.
    const keys = Object.keys(fontMap).join('|');
    const regex = new RegExp(`(?<!-)\\b(${keys})(?!\\S)`, 'g');
    
    content = content.replace(regex, match => fontMap[match.replace(/\\/g, '')]);
    
    fs.writeFileSync(path, content);
    console.log(`Updated ${file}`);
  } else {
    console.log(`File not found: ${path}`);
  }
});
