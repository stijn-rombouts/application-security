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
| **SCA-01** | [`lodash`: Code Injection (CVE-2026-4800)](https://nvd.nist.gov/vuln/detail/CVE-2026-4800) | **Critical** | True Positive | Critical impact on frontend/backend template processing; vulnerable version 4.17.23 is used (CVSS 9.8). |
| **SCA-02** | [`symfony/http-foundation`: Auth Bypass (CVE-2025-64500)](https://nvd.nist.gov/vuln/detail/CVE-2025-64500) | **High** | True Positive | PATH_INFO parsing error can lead to prefix-based authorization bypass (CVSS 7.3). |
| **IAC-01** | Running as Root (Privilege Escalation)| **High** | True Positive | Missing `USER` command in both Dockerfiles (CWE-250). High escape risk. |
| **SCA-03** | `league/commonmark`: XSS / SSRF | **Medium** | True Positive | Vulnerable version if rendering untrusted markdown. |
| **SCA-04** | `psy/psysh`: Local Privilege Escalation | **Medium** | Context-Dep | Only exploitable if attacker can write to the CWD. |
| **IAC-02** | Missing `--no-install-recommends` | **Medium** | True Positive | Increases attack surface and image size. |
| **AUTH-01** | Insufficient Rate Limiting (IP-based) | **High** | True Positive | Rate limiting is only applied per-username (CWE-307). Endpoint is vulnerable to password spraying. |
| **IAC-03** | Missing `HEALTHCHECK` | **Low** | True Positive | Affects availability and monitoring. |

---

## 🛠️ Technical Remediation Guide

### CRITICAL

#### 1. [SCA-01] lodash: Arbitrary Code Injection
*   **CVE / Advisory**: [CVE-2026-4800](https://nvd.nist.gov/vuln/detail/CVE-2026-4800) | [GHSA-35jh-r3h4-6jhm](https://github.com/advisories/GHSA-35jh-r3h4-6jhm)
*   **Severity**: **9.8 Critical** (CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H)
*   **Risk**: Attackers can execute arbitrary JavaScript in the application context by passing crafted key names into `_.template` options imports or exploiting prototype pollution chains.
*   **Remediation**: Update `lodash` to version `4.18.1` or later using `npm audit fix` or by updating `package.json`.

#### 2. [SCA-02] symfony/http-foundation: Authorization Bypass
*   **CVE / Advisory**: [CVE-2025-64500](https://nvd.nist.gov/vuln/detail/CVE-2025-64500) | [FriendsOfPHP Advisory](https://github.com/FriendsOfPHP/security-advisories/blob/master/symfony/http-foundation/CVE-2025-64500.yaml)
*   **Severity**: **7.3 High** (CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:L/I:L/A:L)
*   **Risk**: The `Request` class incorrectly parses the `PATH_INFO` server variable, producing a path without a leading `/`. Firewalls and prefix-based access control rules (such as `/admin`) can be bypassed.
*   **Remediation**: Update Symfony core dependencies to version `>= 7.3.7` (or `6.4.29` / `5.4.50`) using `composer update symfony/http-foundation`.

#### 3. [IAC-01] Containerized Processes Running as Root
*   **Affected Files**: [`code/Dockerfile`](code/Dockerfile#L39-L48) and [`code/Dockerfile.nginx`](code/Dockerfile.nginx#L10-L15)
*   **Risk**: A compromised container process gains root access to the container filesystem and potentially the host machine.
*   **Current Vulnerable Configuration**:
    In [`code/Dockerfile`](code/Dockerfile#L39-L48):
    ```dockerfile
    # Copy entrypoint
    COPY ./php-fpm-entrypoint /usr/local/bin/php-entrypoint

    # Give permissions to everything in bin/
    RUN chmod a+x /usr/local/bin/*

    ENTRYPOINT ["/usr/local/bin/php-entrypoint"]

    CMD ["php-fpm"]
    ```
    Neither `Dockerfile` nor `Dockerfile.nginx` specifies a `USER` directive, causing container processes (`php-fpm` and `nginx`) to run with root privileges (`UID 0`).
*   **What Should Be There**:
    A non-root user should be created and activated with the `USER` instruction before runtime, ensuring the container adheres to the principle of least privilege:
    ```dockerfile
    # Create dedicated user and assign appropriate ownership
    RUN useradd -G www-data,root -u 1000 -d /home/appuser appuser \
        && chown -R appuser:appuser /usr/share/nginx/html

    # Switch to non-root user
    USER appuser

    ENTRYPOINT ["/usr/local/bin/php-entrypoint"]
    CMD ["php-fpm"]
    ```
    For [`code/Dockerfile.nginx`](code/Dockerfile.nginx), either use an unprivileged base image (`nginxinc/nginx-unprivileged:alpine`) or switch to `USER nginx` after granting access to the necessary cache/pid directories and running on an unprivileged port (e.g. 8080).

#### 4. [AUTH-01] Insufficient Rate Limiting (Missing IP-based Throttling)
*   **Affected File**: [`code/app/Livewire/Auth/Login.php`](code/app/Livewire/Auth/Login.php#L72-L96)
*   **Risk**: Vulnerable to "Password Spraying" from a single IP (WSTG-ATHN-03).
*   **Current Vulnerable Code**:
    In [`code/app/Livewire/Auth/Login.php`](code/app/Livewire/Auth/Login.php#L74-L96):
    ```php
    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): ?string
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return null;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        return __('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->username).'|'.request()->ip());
    }
    ```
*   **Why Password Spraying Works**:
    The throttle key `Str::lower($this->username).'|'.request()->ip()` pairs the username with the IP. When an attacker carries out a password spraying attack, they test a single password across hundreds of different usernames from the same IP. Because `$this->username` changes on every attempt, a brand new throttle key is generated each time, and the threshold of 5 attempts is never reached.
*   **What Should Be There**:
    Implement a dual-tier rate limiting strategy that enforces a global IP throttle in addition to the per-account throttle:
    ```php
    protected function ipThrottleKey(): string
    {
        return 'login-ip|'.request()->ip();
    }

    protected function ensureIsNotRateLimited(): ?string
    {
        // 1. Protect specific account (username + IP)
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            $seconds = RateLimiter::availableIn($this->throttleKey());
            return __('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]);
        }

        // 2. Protect login endpoint from IP spraying (e.g., max 20 attempts per minute across any username)
        if (RateLimiter::tooManyAttempts($this->ipThrottleKey(), 20)) {
            $seconds = RateLimiter::availableIn($this->ipThrottleKey());
            return __('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]);
        }

        return null;
    }
    ```
    And in `validateCredentials()`, increment both rate limiters on failure:
    ```php
    RateLimiter::hit($this->throttleKey());
    RateLimiter::hit($this->ipThrottleKey());
    ```

---

## 🔍 DAST Findings: Login Throttling Analysis

Since this application uses CSRF protection, **OWASP ZAP Fuzzer** was used to simulate a brute-force attack on the login endpoint.

*   **Vulnerability [AUTH-01]**: The application triggers throttling when the **same username** is targeted repeatedly. However, when the fuzzer **rotates usernames**, no throttling occurred for the single source IP.
*   **Root Cause**: The component [`code/app/Livewire/Auth/Login.php`](code/app/Livewire/Auth/Login.php#L93-L96) constructs the throttle key as `$this->username . '|' . request()->ip()`. No standalone rate limiting exists for `request()->ip()`.
*   **Result**: The rate limiter is too narrowly scoped. It fails to protect the **login endpoint** itself from automated spamming from a single source.
*   **Impact**: Highly vulnerable to "Credential Stuffing" and "Password Spraying" (WSTG-ATHN-03).


---

## ✅ Verified Secure Configurations
*   **Framework Health**: PHP version 8.4.13 is current.
*   **Information Leakage**: `APP_DEBUG` is set to `false` in production-equivalent environment.
*   **Secrets Management**: `Gitleaks` found no hardcoded credentials in the repository history.
