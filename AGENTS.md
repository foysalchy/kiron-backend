# Project Operational Rules & Guidelines (kiron-backend)

This project is a live **Production** system. Every agent, developer, and automated process working in this repository MUST strictly follow the rules below.

---

### 1. Production Safety & System Integrity (Live Production)
- **Status:** This codebase is currently running in a live production environment.
- **Strict Prohibition:** Never introduce changes that could break existing system logic, active API contracts, scheduled jobs, database integrity, or core workflows.
- **Compatibility:** All modifications must maintain backward compatibility. Verify side effects on existing dependencies, models, controllers, and database tables before proposing or making changes.

### 2. Never Add New Features
- Do **NOT** introduce new features, speculative enhancements, unrequested refactoring, or third-party libraries on your own initiative.
- Strictly adhere to the scope explicitly requested by the user. If an enhancement is considered beneficial, mention it as a note only; do not implement it.

### 3. Mandatory Plan & Approval Before Implementation
- Before modifying or adding any files or code:
  1. Provide a concise, clear, and itemized plan to the user.
  2. List the exact files to be created or modified.
  3. Detail what specific changes will be made in each file.
  4. Note any potential risks or dependencies.
- **Wait for explicit user permission/approval** before writing or modifying any code. Only proceed once approval is given.

### 4. No Extra Additions or Removals (Surgical Changes Only)
- Do **NOT** add extra files, dependencies, boilerplate, or unused helper functions.
- Do **NOT** delete or alter existing code, configurations, or comments unless explicitly required for the task.
- Keep diffs as minimal and surgical as possible.

### 5. Minimal & Concise Permission Prompts
- Keep permission requests and explanations minimal, structured, and easy to review.
- Avoid unnecessary chatter or redundant questions—clearly present the plan, highlight critical impacts, and ask for confirmation directly.

