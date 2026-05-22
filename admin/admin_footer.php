  </div><!-- /page-body -->
</div><!-- /main-content -->

<script>
// Mobile menu toggle
const menuBtn = document.getElementById('menuBtn');
if(window.innerWidth <= 768 && menuBtn) menuBtn.style.display = 'inline-flex';
window.addEventListener('resize', () => {
  if(menuBtn) menuBtn.style.display = window.innerWidth <= 768 ? 'inline-flex' : 'none';
});

// Auto-dismiss alerts
document.querySelectorAll('.alert').forEach(a => {
  setTimeout(() => a.style.opacity = '0', 4000);
  setTimeout(() => a.remove(), 4500);
  a.style.transition = 'opacity .5s';
});

// Confirm deletes
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if(!confirm(el.dataset.confirm)) e.preventDefault();
  });
});
</script>
</body>
</html>
