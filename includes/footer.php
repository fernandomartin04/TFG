<!-- Footer fijo solo en escritorio -->
<footer class="bg-dark text-white text-center p-3 mt-5 d-none d-md-block fixed-bottom">
  <div class="container d-flex justify-content-center flex-wrap gap-3 mb-2">
    <a href="politica_privacidad.php" class="text-white text-decoration-none">Política de Privacidad</a>
    <a href="aviso_legal.php" class="text-white text-decoration-none">Aviso Legal</a>
    <a href="quienes_somos.php" class="text-white text-decoration-none">Quiénes somos</a>
    <a href="contacto.php" class="text-white text-decoration-none">Contacto</a>
  </div>
  <p class="mb-1">&copy; 2025 UrbanWear. Todos los derechos reservados.</p>
  <div>
    <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
    <a href="https://www.instagram.com/fernaandoomg/" class="text-white me-3"><i class="bi bi-instagram"></i></a>
    <a href="#" class="text-white me-3"><i class="bi bi-twitter-x"></i></a>
  </div>
</footer>

<!-- Footer normal en móvil -->
<footer class="bg-dark text-white text-center p-3 mt-5 d-block d-md-none">
  <div class="container d-flex justify-content-center flex-wrap gap-3 mb-2">
    <a href="politica_privacidad.php" class="text-white text-decoration-none">Política de Privacidad</a>
    <a href="aviso_legal.php" class="text-white text-decoration-none">Aviso Legal</a>
    <a href="quienes_somos.php" class="text-white text-decoration-none">Quiénes somos</a>
    <a href="contacto.php" class="text-white text-decoration-none">Contacto</a>
  </div>
  <p class="mb-1">&copy; 2025 UrbanWear. Todos los derechos reservados.</p>
  <div>
    <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
    <a href="https://www.instagram.com/fernaandoomg/" class="text-white me-3"><i class="bi bi-instagram"></i></a>
    <a href="#" class="text-white me-3"><i class="bi bi-twitter-x"></i></a>
  </div>
</footer>

<!-- Ajuste de espacio inferior para evitar solapamiento -->
<style>
  body {
    padding-bottom: 130px; /* deja espacio en escritorio para el footer fijo */
  }

  @media (max-width: 767.98px) {
    body {
      padding-bottom: 0 !important; /* en móvil no se necesita espacio */
    }
  }
</style>

<!-- Aviso de cookies GRANDE -->
<div id="cookie-banner" class="position-fixed bottom-0 start-50 translate-middle-x bg-dark text-white p-4 shadow-lg rounded-top w-100 w-md-75 w-lg-50" style="z-index: 9999; display: none;">
  <div class="text-center">
    <p class="mb-3 fs-5">
      Utilizamos cookies para mejorar tu experiencia y mostrarte contenido personalizado.
      Al continuar navegando, aceptas su uso.
    </p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="politica_privacidad.php" id="saber-mas-cookies" class="btn btn-outline-light">Saber más</a>
      <button id="aceptar-cookies" class="btn btn-primary">Aceptar cookies</button>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script cookies -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  const banner = document.getElementById("cookie-banner");
  const btnAceptar = document.getElementById("aceptar-cookies");
  const enlace = document.getElementById("saber-mas-cookies");

  if (!localStorage.getItem("cookies_aceptadas")) {
    banner.style.display = "block";
  }

  btnAceptar.addEventListener("click", function () {
    localStorage.setItem("cookies_aceptadas", "true");
    banner.style.display = "none";
  });

  enlace.addEventListener("click", function () {
    banner.style.display = "none";
  });
});
</script>

</body>
</html>
