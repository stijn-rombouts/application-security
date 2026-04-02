# Security Audit Guide: Laravel 12 Application

This guide outlines a structured methodology for performing a security audit on your Laravel application, covering **Learning Goals 11.1 and 11.3**.

---

## 🏗️ Audit Methodology (Goal 11.1)

Based on the **OWASP Web Security Testing Guide (WSTG v4.2)**, we will follow a multi-phase approach. For each phase, refer to the corresponding section in your `wstg-v4.2.pdf`.

### Phase 1: Information Gathering (WSTG-INFO)
- **Action**: Identify the framework, web server, and technology stack.
- **WSTG Link**: WSTG-INFO-01 to INFO-10.
- **For Laravel**: Check `composer.json` (Laravel version), `php -v`, and standard Laravel routes like `/login` or `/register`.

### Phase 2: Configuration & Deployment (WSTG-CONF)
- **Action**: Inspect environment settings and infrastructure scripts.
- **WSTG Link**: WSTG-CONF-01 to CONF-11.
- **For Laravel**: Inspect `.env` (APP_DEBUG=true is a critical finding!), `nginx.conf`, and `Dockerfile`.

### Phase 3: Identity & Authentication (WSTG-ATHN)
- **Action**: Audit the login and registration flows.
- **WSTG Link**: WSTG-ATHN-01 to ATHN-10.
- **For Laravel**: Check `config/fortify.php` for password rules and multi-factor auth (MFA) settings.

### Phase 4: Input Validation (WSTG-INPV)
- **Action**: Test for SQL Injection, XSS, and Mass Assignment.
- **WSTG Link**: WSTG-INPV-01 to INPV-19.
- **For Laravel**: Look for `raw()` queries in models, usage of `{!! $variable !!}` (unescaped output in Blade), and `$fillable` or `$guarded` in models.

---

## 🛠️ Security Toolset (Goal 11.3)

To reach these goals, we will use a combination of **SCA**, **SAST**, and **DAST** tools.

### 1. Software Composition Analysis (SCA)
**Purpose**: Identify known vulnerabilities in your dependencies.
- **Tool**: `composer audit`
  - **Run**: `php composer.phar audit` (or just `composer audit`)
- **Tool**: `npm audit`
  - **Run**: `npm audit`

### 2. Static Application Security Testing (SAST)
**Purpose**: Analyze source code for common security anti-patterns.
- **Tool**: **Larastan (PHPStan for Laravel)**
  - **Run**: `vendor/bin/phpstan analyse --level=max` (with security rules)
- **Tool**: **Snyk CLI** (Strongly Recommended)
  - **Run**: `snyk test` (scans code and dependencies)

### 3. Dynamic Application Security Testing (DAST)
**Purpose**: Scan the running application via its HTTP interface.
- **Tool**: **OWASP ZAP** (Desktop or CLI)
  - **Run**: Start with a "Full Scan" against `http://localhost:8000`.
- **Tool**: **Nikto**
  - **Run**: `nikto -h http://localhost:8000` (good for basic server/config flaws).

### 4. Infrastructure & Secrets
- **Tool**: **Trivy** (for `Dockerfile` audit)
  - **Run**: `trivy config .`
- **Tool**: **Gitleaks** (for detecting hardcoded secrets)
  - **Run**: `gitleaks detect --verbose`

---

## 📅 Suggested Audit Plan

1. **Step 1**: Run `composer audit` and `npm audit` to clear low-hanging fruit.
2. **Step 2**: Install `larastan` and run a static analysis scan.
3. **Step 3**: Launch the app with `composer run dev` and perform an "Automated Scan" with **OWASP ZAP**.
4. **Step 4**: Perform a manual walk-through of the authentication flow (Goal 11.1).
5. **Step 5**: Document your findings in the `audit-findings-analysis.md` template.
