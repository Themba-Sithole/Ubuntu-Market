        </div>
        <footer class="portal-footer">
            <p>© <?= date('Y') ?> Ubuntu Market — <?= $portalType === 'admin' ? 'Platform Administration' : 'Seller Workspace' ?></p>
        </footer>
    </div>
</div>

<script src="<?= htmlspecialchars(portal_script_url()) ?>" defer></script>
</body>
</html>
