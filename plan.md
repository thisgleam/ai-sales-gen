# Project Plan: AI Sales Page Generator (Laravel)

## 1. Project Constraints & Rules for AI Agent
- **Strict Deadline:** This project must be production-ready immediately. Speed and reliability are top priorities.
- **Architecture:** Laravel Monolith. Use Laravel Fortify (Blade stack) for quick authentication. DO NOT build a separate SPA (React/Vue) or API unless absolutely necessary. Use TailwindCSS + AlpineJS for styling.
- **LLM Strategy:** DO NOT ask the LLM to generate raw HTML/Tailwind code. Ask the LLM to return strict JSON. We will map the JSON to predefined Blade components.
- **Simplicity:** Do not over-engineer. Stick to the requested features. Ignore bonus features until Phase 1-4 are fully tested and deployed.@

## 2. Database Schema (Phase 1)
Create the following models and migrations:
1.  `User` (Default Laravel Fortify)
2.  `SalesPage`
    - `id`, `user_id` (foreign key)
    - `product_name` (string)
    - `original_input` (json) -> To store the exact form inputs.
    - `generated_content` (json) -> To store the structured JSON returned by the LLM.
    - `status` (string: draft, published)
    - `timestamps`

## 3. Core Features & Implementation Steps (Phase 2)

### Step 2.1: Authentication
- Install Laravel Fortify.
- Ensure Login, Register, Logout routes and views are functional.

### Step 2.2: Product Input Form
- Create a route and view: `/dashboard/create`
- Form fields required:
  - Product/Service Name (text)
  - Description (textarea)
  - Key Features (textarea - comma separated)
  - Target Audience (text)
  - Price (text)
  - Unique Selling Points (textarea)

### Step 2.3: AI Service (The Prompt)
- Create an `AIService` class or action (`app/Services/AIService.php`).
- Integrate with OpenAI/Gemini API.
- **Crucial System Prompt Instruction:**
  "You are a master copywriter. Analyze the following product data and generate a sales page structure. You MUST return ONLY valid JSON in this exact structure, with no markdown formatting or HTML tags:
  {
    "headline": "...",
    "sub_headline": "...",
    "product_description": "...",
    "benefits": ["benefit 1", "benefit 2", "benefit 3"],
    "features": [{"name": "...", "description": "..."}],
    "social_proof_placeholder": "...",
    "pricing_display": "...",
    "call_to_action": "..."
  }"

### Step 2.4: Save & Redirect
- Upon receiving the JSON from the LLM, save the record to the `sales_pages` table.
- Redirect the user to the Live Preview page (`/pages/{id}`).

## 4. Live Preview & Rendering (Phase 3)
- Create a route and view: `/pages/{id}`
- Build a beautiful, responsive TailwindCSS layout (Landing Page style).
- Map the decoded `generated_content` JSON into the Blade layout:
  - Inject `$page->generated_content['headline']` into an `<h1>` tag.
  - Loop through `$page->generated_content['features']` to create a grid of feature cards.
- Add an "Edit/Regenerate" button and a "Back to Dashboard" button.

## 5. Dashboard & Management (Phase 4)
- Update the `/dashboard` route to list all created `SalesPage` records belonging to the authenticated user.
- Include actions: View (Live Preview), Delete.
- Regenerate logic: Allow users to resubmit the `original_input` to the `AIService` and update the `generated_content`.

## 6. Deployment Readiness (Phase 5)
- Ensure all environment variables (DB, LLM API keys) are properly referenced in `.env.example`.
- Ensure database seeders exist if needed for testing.
- Optimize assets (`pnpm build`).