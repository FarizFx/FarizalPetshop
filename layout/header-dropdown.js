document.addEventListener('DOMContentLoaded', function () {
  const profileDropdown = document.getElementById('profileDropdown');
  const dropdownMenu = profileDropdown ? profileDropdown.nextElementSibling : null;

  if (profileDropdown && dropdownMenu) {
    profileDropdown.addEventListener('click', function (e) {
      e.preventDefault();
      dropdownMenu.classList.toggle('show');
      profileDropdown.setAttribute('aria-expanded', dropdownMenu.classList.contains('show'));
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
      if (!profileDropdown.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
        profileDropdown.setAttribute('aria-expanded', 'false');
      }
    });
  }
});
