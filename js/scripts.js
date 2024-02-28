document.addEventListener("DOMContentLoaded", function () {
  var splide = new Splide(".splide", {
    type: "loop",
    autoplay: true,
    drag: true,
  });
  splide.mount();
});
