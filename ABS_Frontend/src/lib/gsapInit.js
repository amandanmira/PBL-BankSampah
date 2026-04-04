import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

export const initScrollAnimations = () => {
  // Membersihkan animasi ScrollTrigger sebelumnya agar tidak menumpuk saat pindah halaman
  ScrollTrigger.getAll().forEach(trigger => trigger.kill());

  // === 1. Animasi Fade Biasa (Tunggal) ===
  const elements = document.querySelectorAll('.gsap-fade-up');
  elements.forEach((el) => {
    gsap.fromTo(el,
      { y: 40, opacity: 0 },
      {
        y: 0,
        opacity: 1,
        duration: 0.8,
        ease: "power3.out",
        scrollTrigger: {
          trigger: el,
          start: "top 85%", // Animasi jalan pas elemen terlihat 15% di viewport
          toggleActions: "play none none none" // Hanya dimainkan satu kali aja
        }
      }
    );
  });

  // === 2. Animasi Stagger (Muncul Bergantian Secara Elegan) ===
  const staggerParents = document.querySelectorAll('.gsap-stagger-parent');
  staggerParents.forEach((parent) => {
    const items = parent.querySelectorAll('.gsap-stagger-item');
    if (items.length > 0) {
      gsap.fromTo(items,
        { y: 60, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 0.8,
          stagger: 0.15, // Kuncinya ada di sini! Jeda antar card saat muncul
          ease: "power3.out",
          scrollTrigger: {
            trigger: parent, // Trigger animasinya adalah sang parent pembungkus
            start: "top 82%",
            toggleActions: "play none none none"
          }
        }
      );
    }
  });
};
