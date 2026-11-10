SHAI PANEL - owner login (no hard-coded account)

1. Upload/extract everything into public_html and open the site.
2. If the panel has no owner yet, the login page sends you to /setup.
3. Open writable/.shai_setup_token in your file manager, copy its content,
   and paste it on the setup page. Pick your own name, username, e-mail and
   password (10+ characters, letters and numbers).
4. The token file is deleted after the account is created and /setup closes.

Optional .env settings
  shai.registration = closed     # turn off public sign-ups; only you create accounts
Security notes
  - 5 wrong passwords for one name (or 20 from one IP) lock logins for 15 minutes.
  - New passwords are hashed with a stronger cost; old ones upgrade at next login.
  - Security headers are added to every page except /connect and /loader/catalog.
