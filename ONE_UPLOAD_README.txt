SHAI PANEL | x10Hosting deployment

Target domain: freeshai.x10.mx
Extract the contents directly into:
domains/freeshai.x10.mx/public_html/

index.php and .env must appear at the document root.
The first page request initializes tables in the existing database.
Existing database records are not deleted by the bundled installer.

The configured database, user, and host use your previously provided settings.
Your database credentials were provided earlier and should be rotated.
Once rotated, update .env with the new password.

The domain has changed from shai.runs-on.dev to freeshai.x10.mx.
No redirects to the former domain are included in this archive.
Any DirectAdmin Site Redirection entries pointing to the former domain
must be removed in the hosting dashboard separately.

A working Let's Encrypt certificate for freeshai.x10.mx is required
to use HTTPS; a ZIP cannot install or issue hosting SSL certificates.
The panel keeps HTTPS as its configured public URL.
