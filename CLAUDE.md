# Project Instructions

## Code Formatting

-   Always use tab size 4 for indentation
-   After every edit, format HTML and PHP in blade files properly
-   Indent parent and child elements correctly (proper nesting of divs, sections, etc.)
-   Don't break single html attributes in multiple line just keep them in same line.

## JavaScript Guidelines

-   Always use `jQuery` instead of `$` when adding jQuery code
-   Never use inline JavaScript (onclick, onsubmit, etc.) - always use event handlers
-   Never use vanilla JavaScript `confirm()` - use SweetAlert2 modal instead

## Component Usage

-   Don't add custom elements/components - everything is already present in `html/template/`
-   There are various templates available for users list with actions, bulk actions, etc.
-   Always check `html/template/` files first before making any custom components
-   We should not require any more components than what exists in the template

## User Management

-   Don't mix admin users or staff with student users
-   Admin/staff and students must be managed separately
-   Students should be able to login to their own portal (separate from admin)

## Design Guidelines

-   Follow the design of the admin panel strictly using `html/template` components
-   Only add minimal custom CSS if and only if absolutely required
-   The Cuba Admin Panel template should be the single source of truth for UI components

## List Action Icons

When showing action buttons in list/table views (edit, view, delete), use the following icons from `html/template/user-list.html` (lines 1101-1107):

```html
<div class="common-align gap-2 justify-content-start">
    <!-- View Icon -->
    <a class="square-white" href="#">
        <svg>
            <use href="{{ asset('assets/svg/icon-sprite.svg#eye') }}"></use>
        </svg>
    </a>
    <!-- Edit Icon -->
    <a class="square-white" href="#">
        <svg>
            <use href="{{ asset('assets/svg/icon-sprite.svg#edit-content') }}"></use>
        </svg>
    </a>
    <!-- Delete Icon (using SweetAlert2 confirmation) -->
    <form action="{{ route('admin.example.destroy', $item) }}" method="POST" class="d-inline delete-form">
        @csrf
        @method('DELETE')
        <button type="button" class="square-white trash-7 border-0 bg-transparent p-0 delete-confirm" title="Delete" data-name="{{ $item->name }}">
            <svg>
                <use href="{{ asset('assets/svg/icon-sprite.svg#trash1') }}"></use>
            </svg>
        </button>
    </form>
</div>
```

**Important Notes:**
- Use `type="button"` (not `type="submit"`) for delete buttons
- Add `delete-confirm` class to trigger SweetAlert2 modal
- Add `data-name` attribute for personalized confirmation message
- Add `border-0 bg-transparent p-0` classes to remove default button styling
- The delete confirmation handler is defined in `layouts/app.blade.php` using jQuery
