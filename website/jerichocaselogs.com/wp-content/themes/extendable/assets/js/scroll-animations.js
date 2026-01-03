/**
 * JerichoCaseLogs - GSAP Scroll Animations
 * Cinematic scroll-triggered animations for landing page
 */

(function() {
  'use strict';
  
  // Wait for GSAP and ScrollTrigger to load
  document.addEventListener('DOMContentLoaded', function() {
    
    // Check if GSAP is loaded
    if (typeof gsap === 'undefined') {
      console.warn('GSAP not loaded. Scroll animations will not work.');
      return;
    }
    
    // Register ScrollTrigger plugin
    if (typeof ScrollTrigger !== 'undefined') {
      gsap.registerPlugin(ScrollTrigger);
    } else {
      console.warn('ScrollTrigger not loaded. Using fallback animations.');
      useFallbackAnimations();
      return;
    }
    
    // ====================
    // HEADER SCROLL EFFECT
    // ====================
    const header = document.querySelector('.site-header');
    if (header) {
      window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
          header.classList.add('scrolled');
        } else {
          header.classList.remove('scrolled');
        }
      });
    }
    
    // ====================
    // FADE IN ANIMATIONS
    // ====================
    gsap.utils.toArray('.scroll-fade-in').forEach(element => {
      gsap.from(element, {
        opacity: 0,
        y: 50,
        duration: 1,
        scrollTrigger: {
          trigger: element,
          start: 'top 80%',
          toggleActions: 'play none none reverse'
        }
      });
    });
    
    // ====================
    // SLIDE IN FROM LEFT
    // ====================
    gsap.utils.toArray('.scroll-slide-left').forEach(element => {
      gsap.from(element, {
        opacity: 0,
        x: -100,
        duration: 0.8,
        scrollTrigger: {
          trigger: element,
          start: 'top 80%',
          toggleActions: 'play none none reverse'
        }
      });
    });
    
    // ====================
    // SLIDE IN FROM RIGHT
    // ====================
    gsap.utils.toArray('.scroll-slide-right').forEach(element => {
      gsap.from(element, {
        opacity: 0,
        x: 100,
        duration: 0.8,
        scrollTrigger: {
          trigger: element,
          start: 'top 80%',
          toggleActions: 'play none none reverse'
        }
      });
    });
    
    // ====================
    // SCALE UP ANIMATION
    // ====================
    gsap.utils.toArray('.scroll-scale').forEach(element => {
      gsap.from(element, {
        opacity: 0,
        scale: 0.8,
        duration: 0.8,
        scrollTrigger: {
          trigger: element,
          start: 'top 80%',
          toggleActions: 'play none none reverse'
        }
      });
    });
    
    // ====================
    // PARALLAX BACKGROUNDS
    // ====================
    gsap.utils.toArray('.parallax-bg').forEach(element => {
      gsap.to(element, {
        y: '30%',
        ease: 'none',
        scrollTrigger: {
          trigger: element.parentElement,
          start: 'top top',
          end: 'bottom top',
          scrub: true
        }
      });
    });
    
    // ====================
    // HERO SECTION ANIMATIONS
    // ====================
    const heroSection = document.querySelector('.section-hero');
    if (heroSection) {
      // Parallax effect on hero elements
      const heroContent = heroSection.querySelector('.hero-content');
      const hero3D = heroSection.querySelector('.hero-3d');
      
      if (heroContent) {
        gsap.to(heroContent, {
          y: '50%',
          opacity: 0.3,
          scrollTrigger: {
            trigger: heroSection,
            start: 'top top',
            end: 'bottom top',
            scrub: true
          }
        });
      }
      
      if (hero3D) {
        gsap.to(hero3D, {
          scale: 1.2,
          rotation: 15,
          scrollTrigger: {
            trigger: heroSection,
            start: 'top top',
            end: 'bottom top',
            scrub: true
          }
        });
      }
    }
    
    // ====================
    // FEATURE CARDS STAGGER
    // ====================
    const featureCards = gsap.utils.toArray('.feature-card');
    if (featureCards.length > 0) {
      gsap.from(featureCards, {
        opacity: 0,
        y: 100,
        stagger: 0.2,
        duration: 1,
        scrollTrigger: {
          trigger: featureCards[0].parentElement,
          start: 'top 70%',
          toggleActions: 'play none none reverse'
        }
      });
    }
    
    // ====================
    // COUNTER ANIMATIONS
    // ====================
    gsap.utils.toArray('.stat-number').forEach(element => {
      const endValue = parseInt(element.textContent);
      const obj = { val: 0 };
      
      gsap.to(obj, {
        val: endValue,
        duration: 2,
        scrollTrigger: {
          trigger: element,
          start: 'top 80%',
          once: true
        },
        onUpdate: function() {
          element.textContent = Math.ceil(obj.val);
        }
      });
    });
    
    // ====================
    // 3D MODEL ROTATION ON SCROLL
    // ====================
    const modelContainers = gsap.utils.toArray('.model-3d-container');
    modelContainers.forEach(container => {
      gsap.to(container, {
        rotation: 360,
        scrollTrigger: {
          trigger: container,
          start: 'top bottom',
          end: 'bottom top',
          scrub: 2
        }
      });
    });
    
    // ====================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ====================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          e.preventDefault();
          gsap.to(window, {
            duration: 1,
            scrollTo: { y: targetElement, offsetY: 80 },
            ease: 'power2.inOut'
          });
        }
      });
    });
    
    // ====================
    // TEXT REVEAL ANIMATION
    // ====================
    gsap.utils.toArray('.text-reveal').forEach(element => {
      const words = element.textContent.split(' ');
      element.innerHTML = words.map(word => `<span class="word">${word}</span>`).join(' ');
      
      gsap.from(element.querySelectorAll('.word'), {
        opacity: 0,
        y: 20,
        stagger: 0.05,
        duration: 0.6,
        scrollTrigger: {
          trigger: element,
          start: 'top 80%',
          toggleActions: 'play none none reverse'
        }
      });
    });
    
    console.log('GSAP scroll animations initialized');
  });
  
  // ====================
  // FALLBACK ANIMATIONS (NO GSAP)
  // ====================
  function useFallbackAnimations() {
    const observerOptions = {
      root: null,
      rootMargin: '0px',
      threshold: 0.1
    };
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    }, observerOptions);
    
    // Observe all elements with animation classes
    document.querySelectorAll('.scroll-fade-in, .scroll-slide-left, .scroll-slide-right, .scroll-scale').forEach(el => {
      observer.observe(el);
    });
    
    console.log('Using fallback scroll animations (IntersectionObserver)');
  }
  
})();
