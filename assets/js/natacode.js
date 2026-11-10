$(".after-card").hide();
$(document).ready(function () {
  $(".after-card").fadeIn("slow");
  $("input").change(function (e) {
    e.preventDefault();
    $(".form-text, .alert-danger, .form-group .text-danger").hide();
  });
});

const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  showCloseButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    // Pause on hover only for mouse devices. On touch a tap fires mouseenter
    // but never mouseleave, which left the toast stuck open.
    if (window.matchMedia && window.matchMedia("(hover: hover) and (pointer: fine)").matches) {
      toast.addEventListener("mouseenter", Swal.stopTimer);
      toast.addEventListener("mouseleave", Swal.resumeTimer);
    }
  },
});
