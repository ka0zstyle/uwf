// Custom JavaScript for UltraWebForge

(function($) {
  "use strict";

  // Preloader
  $(window).on('load', function() {
    if ($('.js-preloader').length) {
      $('.js-preloader').delay(100).fadeOut(500);
    }
  });

  // Mobile menu toggle
  $('.menu-trigger').on('click', function() {
    $(this).toggleClass('active');
    $('.header-area .nav').toggleClass('active');
    $('body').toggleClass('menu-open');
  });

  // Close mobile menu when clicking on a link
  $('.header-area .nav li a').on('click', function() {
    if ($(window).width() <= 767) {
      $('.menu-trigger').removeClass('active');
      $('.header-area .nav').removeClass('active');
      $('body').removeClass('menu-open');
    }
  });

  // Smooth scrolling for anchor links using event delegation with optimized performance
  $(document).on('click', 'a[href*="#"]:not([href="#"]):not([href="#0"])', function(event) {
    if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') 
        && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
      if (target.length) {
        event.preventDefault();
        $('html, body').animate({
          scrollTop: target.offset().top - 80
        }, 600, 'swing');
      }
    }
  });

  // Sticky header on scroll with optimized performance
  let ticking = false;
  let lastScrollTop = 0;
  
  function updateHeader() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    if (scrollTop > 100) {
      $('.header-area').addClass('sticky background-header');
      // Show scroll to top button
      $('#scroll-to-top').addClass('visible').css('display', 'flex');
    } else {
      $('.header-area').removeClass('sticky background-header');
      // Hide scroll to top button
      $('#scroll-to-top').removeClass('visible').css('display', 'none');
    }
    
    lastScrollTop = scrollTop;
    ticking = false;
  }
  
  $(window).on('scroll', function() {
    if (!ticking) {
      window.requestAnimationFrame(updateHeader);
      ticking = true;
    }
  });

})(jQuery);
