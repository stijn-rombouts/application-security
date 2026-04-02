# Security Audit Findings & Analysis Report

**Target Application**: Laravel 12 Web Application
**Audit Date**: October 2023
**Scope**: SCA (Dependencies), SAST (Source Code), IaC (Docker Infrastructure)

This report documents the vulnerabilities identified during the security audit, their triage status (Goal 11.2), and technical recommendations (Goal 11.4).

---

## 📝 Audit Activity Log (Goal 11.1)

The following steps were performed as part of the structured security audit:

### Phase 1: Information Gathering
- **Technological Stack**: Verified application runs on **PHP 8.4.13**.
- **Entry Points**: Identified authenticated routes:
  - **Login**: `/` (Homepage)
  - **Logout**: `/logout`

### Phase 2: Configuration & Deployment Review
- **Environment**: Inspected `.env` files; confirmed `APP_DEBUG=false` in production-equivalent environment.
- **Infrastructure**: Audited server and container configurations:
  - `nginx.conf`: Web server configuration.
  - `Dockerfile` (PHP-FPM) & `Dockerfile.nginx`: Container specifications.

### Phase 3: Authentication & Identity
- **Framework**: Identified **Laravel Fortify** as the authentication provider.
- **Security Controls**:
  - **2FA**: Two-factor authentication is currently disabled per business requirements.
  - **Throttling**: Identified rate limiting configuration in `fortify.php` (Found to be scoped only to username, allowing IP-based endpoint spamming).

### Phase 4: Automated Vulnerability Scanning
- **SCA (Software Composition Analysis)**:
  - `composer audit`: Found 6 vulnerabilities (Commonmark, Symfony, PsySH).
  - `npm audit`: Found 1 high-severity vulnerability (Lodash).
  - `Snyk`: Verified findings and identified additional paths for Lodash and Symfony vulnerabilities.
- **SAST (Static Application Security Testing)**:
  - `Larastan`: Performed code quality and security analysis (220 errors found).
- **Secret Scanning**:
  - `Gitleaks`: Scanned repository history; found **0 leaks**.

### Phase 5: Infrastructure Scanning
- **Container Audit**:
  - `Trivy`: Scanned Dockerfiles; identified high-risk misconfigurations (Root user usage).

### Phase 6: Local DAST Environment Setup (Goal 11.3)
To perform dynamic testing, the production-equivalent Docker environment was provisioned locally.

**Commands Executed**:
```bash
# 1. Modify Nginx for HTTP (Port 80)
sed -i 's/listen 443 ssl/listen 80/' nginx.conf
sed -i 's/EXPOSE 443/EXPOSE 80/' Dockerfile.nginx
sed -i 's/443:443/80:80/' compose.test.prod.yml

# 2. Key Generation & Environment Setup
cp .env.test.prod .env
APP_KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;")
sed -i "s|APP_KEY=.*|APP_KEY=$APP_KEY|" .env
sed -i "s|APP_URL=.*|APP_URL=http://localhost|" .env

# 3. Build Images
docker build -t mpi-employment-interest-tool:latest -f Dockerfile .
docker build -t custom-nginx:latest -f Dockerfile.nginx .
```

- **Repository**: [MPI-001 Employment Interest Tool](https://github.com/Thomas-More-Digital-Innovation/2526-MPI-001-Employment-Interest-Tool)
- **Modifications**:
  1. Updated `nginx.conf`: Changed port to `80`, disabled SSL/HTTPS.
  2. Updated `Dockerfile.nginx`: Exposed port `80`.
  3. Updated `compose.test.prod.yml`: Set port mapping to `80:80`, removed SSL volumes.

---

## ✅ Verification Status (Model Verification)

I have manually executed the security tools to verify the findings reported by the USER.

### 📦 Dependency Verification
- **`composer audit`**: **VERIFIED**. Confirmed 6 security advisories affecting `league/commonmark`, `symfony/http-foundation`, `psy/psysh`, and `phpunit`.
- **`npm audit`**: **VERIFIED**. Confirmed 1 high-severity vulnerability in `lodash` (Code Injection/Prototype Pollution).
- **`snyk test`**: **VERIFIED**. Confirmed vulnerabilities in `lodash` (High), `symfony/http-foundation` (Medium), and `league/commonmark` (Medium).

### 🏗️ Infrastructure Verification
- **`trivy config Dockerfile`**: **VERIFIED**. Found 3 issues, including `DS-0002` (High: Root user) and `DS-0029` (High: Missing `--no-install-recommends`).
- **`trivy config Dockerfile.nginx`**: **VERIFIED**. Found 2 issues, including `DS-0002` (High: Root user).

### 🔑 Configuration Verification
- **APP_KEY**: Successfully generated a new Base64 key and updated the local `.env`.
- **APP_URL**: Confirmed set to `http://localhost`.

---

## 🚦 Vulnerability Triage (Goal 11.2)

The following vulnerabilities were identified using **Composer Audit**, **NPM Audit**, **Snyk**, and **Trivy**.

| Finding ID | Vulnerability | Severity | Status | Justification |
| :--- | :--- | :--- | :--- | :--- |
| **SCA-01** | `lodash`: Code Injection | **High** | True Positive | Critical impact on frontend security; vulnerable version 4.17.23 is used. |
| **SCA-02** | `symfony/http-foundation`: Auth Bypass | **High** | True Positive | PATH_INFO parsing error can lead to authorization bypass. |
| **IAC-01** | Running as Root (Privilege Escalation) | **High** | True Positive | Missing `USER` command in both Dockerfiles. High escape risk. |
| **SCA-03** | `league/commonmark`: XSS / SSRF | **Medium** | True Positive | Vulnerable version if rendering untrusted markdown. |
| **SCA-04** | `psy/psysh`: Local Privilege Escalation | **Medium** | Context-Dep | Only exploitable if attacker can write to the CWD. |
| **IAC-02** | Missing `--no-install-recommends` | **Medium** | True Positive | Increases attack surface and image size. |
| **AUTH-01** | Insufficient Rate Limiting (IP-based) | **High** | True Positive | Rate limiting is only applied per-username. Endpoint is vulnerable to password spraying/spam from a single IP. |
| **IAC-03** | Missing `HEALTHCHECK` | **Low** | True Positive | Affects availability and monitoring. |

---

## 🛠️ Technical Remediation Guide (Goal 11.4)

### 🔴 CRITICAL / HIGH SEVERITY

#### 1. [SCA-01] lodash: Arbitrary Code Injection
- **Risk**: Attackers can execute arbitrary JavaScript in the context of the application.
- **Reference**: WSTG-INPV-11 (Code Injection).
- **Remediation**: 
  - Update `lodash` to version 4.18.1 or later.
  - Run `npm audit fix` to automatically resolve dependency tree issues.
- **Verification**: Run `npm audit` to confirm the package shows no vulnerabilities.

#### 2. [SCA-02] symfony/http-foundation: Authorization Bypass
- **Risk**: Attackers may bypass application security checks by manipulating the URL path.
- **Reference**: WSTG-ATHZ-02 (Bypass Authorization Schema).
- **Remediation**:
  - Update Symfony core dependencies.
  - Run `composer update symfony/http-foundation` to pull version >= 7.3.7.
- **Verification**: Verify that routes with dual slashes or encoded characters are handled correctly by Laravel middleware.

#### 3. [IAC-01] Containerized Processes Running as Root
- **Risk**: A compromised container process gains root access to the container filesystem and potentially the host machine.
- **Reference**: WSTG-CONF-02 (Infrastructure Misconfiguration).
- **Remediation**:
  - Create a dedicated non-root user in both `Dockerfile` and `Dockerfile.nginx`.
  - **Example Fix**:
    ```dockerfile
    RUN useradd -G www-data,root -u 1000 -d /home/appuser appuser
    USER appuser
    ```
- **Verification**: Run `docker exec [container_id] whoami`. Output must NOT be `root`.

#### 4. [AUTH-01] Insufficient Rate Limiting (Missing IP-based Throttling)
- **Risk**: Attackers can perform "Password Spraying" (trying one password against many usernames) or spam the login endpoint from a single IP without being blocked.
- **Reference**: WSTG-ATHN-03 (Testing for Brute Force).
- **Remediation**:
  - Update the `RateLimiter` definition in `FortifyServiceProvider.php`. 
  - Ensure the limiter key includes both username AND client IP (e.g., `Limit::perMinute(5)->by($request->email.$request->ip())`).
  - Consider adding a global "emergency" throttle for the login route to mitigate massive automated spamming.
- **Verification**: Re-run ZAP Fuzzer using a list of *different* usernames and confirm that the source IP is successfully throttled (e.g., observing the "Too many login attempts" error message) after the threshold is reached.

---

### 🟡 MEDIUM SEVERITY

#### 4. [SCA-03] league/commonmark: XSS / SSRF
- **Risk**: Potential for Cross-Site Scripting if user-provided markdown is rendered.
- **Remediation**: Update `league/commonmark` to version 2.8.2+.
- **Verification**: Re-run `composer audit`.

#### 5. [IAC-02] Missing apt-get '--no-install-recommends'
- **Risk**: Installation of unnecessary packages increases the attack surface of the image.
- **Remediation**: Add `--no-install-recommends` to all `apt-get install` commands.
- **Example Fix**:
  ```dockerfile
  RUN apt-get update && apt-get install -y --no-install-recommends \
      libzip-dev \
      libpng-dev ...
  ```

---

## 🛡️ DAST Findings: Brute Force & Rate Limiting (Goal 11.3)

Since this application uses CSRF protection (`419` error for non-browser POSTs), **OWASP ZAP Fuzzer** was used to simulate a brute-force attack on the login endpoint.

### Audit Outcome (Goal 11.1):
- **Vulnerability [AUTH-01]**: The application correctly triggers throttling when the **same username** is targeted repeatedly. However, when the fuzzer was configured to **rotate usernames**, no throttling occurred for the single source IP.
- **Result**: The rate limiter is too narrowly scoped. While it provides per-account protection, it fails to protect the **login endpoint** from password spraying and automated spamming from a single source.
- **Impact**: Highly vulnerable to "Credential Stuffing" and "Password Spraying" (WSTG-ATHN-03).

---

## ✅ Verified Secure Configurations
- **Framework Health**: PHP version 8.4.13 is current.
- **Information Leakage**: `APP_DEBUG` is set to `false` in production-equivalent environment (Good!).
- **Secrets Management**: `Gitleaks` found no hardcoded credentials in the repository.
