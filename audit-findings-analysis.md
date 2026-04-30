# Security Audit Findings & Analysis Report

**Target Application**: Laravel 12 Web Application
**Audit Date**: October 2023
**Scope**: SCA (Dependencies), SAST (Source Code), IaC (Docker Infrastructure), DAST (Dynamic Testing)

This report documents the vulnerabilities identified during the security audit and provides technical recommendations for remediation.

---

## 🛠️ Security Audit Methodology & Tooling

The audit used a multi-layered approach combining automated scanning and manual verification. Below are the tools employed and how they were applied.

### Software Composition Analysis (SCA)
*   **`composer audit`**: 
    *   **Purpose**: Checks the application's PHP dependencies against the GitHub Advisory Database.
    *   **Usage**: Executed `composer audit` to identify vulnerabilities in the `composer.lock` file.
*   **`npm audit`**: 
    *   **Purpose**: Scans the JavaScript dependency tree for known security vulnerabilities.
    *   **Usage**: Executed `npm audit` to analyze `package.json` and `package-lock.json`.
*   **`Snyk`**: 
    *   **Purpose**: A comprehensive security platform that identifies vulnerability paths and provides remediation advice.
    *   **Usage**: Used `snyk test` to cross-verify findings from internal audit tools and identify specific exploit paths.

### Static Application Security Testing (SAST)
*   **`Larastan`**: 
    *   **Purpose**: A static analysis wrapper for PHPStan specifically designed for Laravel.
    *   **Usage**: Scanned the source code to find potential logic errors and security anti-patterns.
*   **`Gitleaks`**: 
    *   **Purpose**: Specifically designed to find secrets, API keys, and tokens in git history.
    *   **Usage**: Ran a full repository scan to ensure no credentials were accidentally committed.

### Infrastructure & Container Security
*   **`Trivy`**: 
    *   **Purpose**: A versatile security scanner for container images and configuration files (IaC).
    *   **Usage**: Scanned `Dockerfile` and `Dockerfile.nginx` for misconfigurations like root user execution and insecure base images.

### Dynamic Application Security Testing (DAST)
*   **`OWASP ZAP Fuzzer`**: 
    *   **Purpose**: A web fuzzer used to send payloads to application endpoints to test for vulnerabilities like brute-force and injection.
    *   **Usage**: Configured to rotate usernames and passwords against the login endpoint to test rate limiting effectiveness.

---

## 🚦 Vulnerability Triage

The following vulnerabilities were identified and verified as True Positives.

| Finding ID | Vulnerability | Severity | Status | Justification |
| :--- | :--- | :--- | :--- | :--- |
| **SCA-01** | `lodash`: Code Injection | **High** | True Positive | Critical impact on frontend security; vulnerable version 4.17.23 is used. |
| **SCA-02** | `symfony/http-foundation`: Auth Bypass | **High** | True Positive | PATH_INFO parsing error can lead to authorization bypass. |
| **IAC-01** | Running as Root (Privilege Escalation) | **High** | True Positive | Missing `USER` command in both Dockerfiles. High escape risk. |
| **SCA-03** | `league/commonmark`: XSS / SSRF | **Medium** | True Positive | Vulnerable version if rendering untrusted markdown. |
| **SCA-04** | `psy/psysh`: Local Privilege Escalation | **Medium** | Context-Dep | Only exploitable if attacker can write to the CWD. |
| **IAC-02** | Missing `--no-install-recommends` | **Medium** | True Positive | Increases attack surface and image size. |
| **AUTH-01** | Insufficient Rate Limiting (IP-based) | **High** | True Positive | Rate limiting is only applied per-username. Endpoint is vulnerable to password spraying. |
| **IAC-03** | Missing `HEALTHCHECK` | **Low** | True Positive | Affects availability and monitoring. |

---

## 🛠️ Technical Remediation Guide

### 🔴 CRITICAL / HIGH SEVERITY

#### 1. [SCA-01] lodash: Arbitrary Code Injection
*   **Risk**: Attackers can execute arbitrary JavaScript in the context of the application.
*   **Remediation**: Update `lodash` to version 4.18.1 or later using `npm audit fix`.

#### 2. [SCA-02] symfony/http-foundation: Authorization Bypass
*   **Risk**: Attackers may bypass application security checks by manipulating the URL path.
*   **Remediation**: Update Symfony core dependencies to version >= 7.3.7 using `composer update`.

#### 3. [IAC-01] Containerized Processes Running as Root
*   **Risk**: A compromised container process gains root access to the container filesystem and potentially the host machine.
*   **Remediation**: Create a dedicated non-root user in both `Dockerfile` and `Dockerfile.nginx`.
*   **Example**:
    ```dockerfile
    RUN useradd -G www-data,root -u 1000 -d /home/appuser appuser
    USER appuser
    ```

#### 4. [AUTH-01] Insufficient Rate Limiting (Missing IP-based Throttling)
*   **Risk**: Vulnerable to "Password Spraying" from a single IP.
*   **Remediation**: Update `RateLimiter` in `FortifyServiceProvider.php` to include client IP in the throttle key (e.g., `by($request->email.$request->ip())`).

---

## 🔍 DAST Findings: Login Throttling Analysis

Since this application uses CSRF protection, **OWASP ZAP Fuzzer** was used to simulate a brute-force attack on the login endpoint.

*   **Vulnerability [AUTH-01]**: The application triggers throttling when the **same username** is targeted repeatedly. However, when the fuzzer **rotates usernames**, no throttling occurred for the single source IP.
*   **Result**: The rate limiter is too narrowly scoped. It fails to protect the **login endpoint** itself from automated spamming from a single source.
*   **Impact**: Highly vulnerable to "Credential Stuffing" and "Password Spraying" (WSTG-ATHN-03).

---

## ✅ Verified Secure Configurations
*   **Framework Health**: PHP version 8.4.13 is current.
*   **Information Leakage**: `APP_DEBUG` is set to `false` in production-equivalent environment.
*   **Secrets Management**: `Gitleaks` found no hardcoded credentials in the repository history.
