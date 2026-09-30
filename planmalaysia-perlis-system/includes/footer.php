<?php
/**
 * ========================================================================
 * TEMPLAT KAKI LAMAN (footer.php)
 * JABATAN PERANCANGAN BANDAR DAN DESA NEGERI PERLIS (PLANMalaysia @ Perlis)
 * ========================================================================
 */
?>
<?php if (isLoggedIn()): ?>
        </div><!-- /.container-fluid -->
    </main><!-- /.page-content -->
</div><!-- /#wrapper -->

<!-- FOOTER RASMI KERAJAAN -->
<footer class="gov-footer bg-navy text-white-50 py-3 border-top border-secondary">
    <div class="container-fluid px-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 small">
        <div>
            &copy; <?= date('Y') ?> <strong><?= APP_DEPT ?></strong>. Hak Cipta Terpelihara.
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-warning">Kerajaan Negeri Perlis</span>
            <span class="text-muted">|</span>
            <a href="#" class="text-white-50 text-decoration-none hover-white" data-bs-toggle="modal" data-bs-target="#aboutModal">Dasar Keselamatan</a>
            <span class="text-muted">|</span>
            <span>Versi <?= APP_VERSION ?></span>
        </div>
    </div>
</footer>

<!-- MODAL MENGENAI SISTEM & MAKLUMAT KERAJAAN -->
<div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title fw-bold" id="aboutModalLabel">
                    <i class="bi bi-shield-check me-2 text-warning"></i>Mengenai Sistem
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <img src="../assets/images/logo.svg" alt="PLANMalaysia Perlis" height="60" class="mb-3">
                <h5 class="fw-bold text-navy mb-1"><?= APP_NAME ?></h5>
                <p class="text-muted small mb-3"><?= APP_DEPT ?> (<?= APP_STATE ?>)</p>
                <hr>
                <div class="text-start small text-secondary">
                    <p class="mb-1"><strong>Arkitektur:</strong> PHP 8.x + MySQL + IIS Windows Server 2019</p>
                    <p class="mb-1"><strong>Keselamatan:</strong> PDO Prepared Statements, CSRF Protection, Bcrypt Password Hashing, Session Fixation Prevention</p>
                    <p class="mb-1"><strong>Pematuhan:</strong> Garis Panduan Keselamatan ICT Sektor Awam MAMPU & Arkitektur Kerajaan Malaysia</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
</main><!-- /.login-wrapper -->
<?php endif; ?>

<!-- Bootstrap 5.3 JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Skrip Kustom PLANMalaysia Perlis -->
<script src="../assets/js/app.js"></script>
</body>
</html>
