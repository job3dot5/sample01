# sample01

## Local development setup

This project uses HTTPS locally with the custom domain `sample01.dev`

### 1. Install mkcert (one-time setup)

Run the following commands:

`sudo apt update`

`sudo apt install mkcert libnss3-tools`

Initialize the local Certificate Authority: `mkcert -install`

---

### 2. Generate the local SSL certificate

From any directory, run: `mkcert sample01.dev`

This will generate two files:
- `sample01.dev.pem` (certificate)
- `sample01.dev-key.pem` (private key)

---

### 3. Copy certificates into the project

From the directory where the certificates were generated, run:

`mv sample01.dev.pem [project-path]/docker/apache/ssl/sample01.dev.crt`

`mv sample01.dev-key.pem [project-path]/docker/apache/ssl/sample01.dev.key`

Important: these files are local-only and must NOT be committed to Git.

---

### 4. Configure local DNS

Edit the local hosts file as root: `sudo vim /etc/hosts`

Add the following line: `127.0.0.1 sample01.dev`

---

### 5. Start the project

Run: `docker compose up -d --build`

Then open the following URL in your browser: https://sample01.dev

You should see a valid HTTPS connection with no browser warning.

---

### Optional troubleshooting

If mkcert was previously installed manually and the command does not resolve correctly, run:
`hash -r`

Then retry the mkcert commands.

