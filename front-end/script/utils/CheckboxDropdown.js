class CheckboxDropdown {
    constructor(elId) {
        this.elId = elId;
        this.dropdown = document.getElementById(`selectDropdown-${elId}`);

        if (!this.dropdown) {
            return;
        }

        this.button = this.dropdown.querySelector(".dropdown-btn");
        this.checkboxes = this.dropdown.querySelectorAll("input[type='checkbox']");
        this.customScrollbar = this.dropdown.querySelector(".custom-scrollbar-select");
        this.defaultText = this.button.textContent;
        this.firstAppear = true;
        this.isScrolling = false;
        this.scrollTimeout = null;

        this.init();
    }

    init() {
        this.button.addEventListener("click", () => {
            this.dropdown.classList.toggle("open");
        });

        this.checkboxes.forEach(checkbox => {
            checkbox.addEventListener("change", () => {
                this.updateButtonText();
            });
        });

        document.addEventListener("click", event => {
            if (!this.dropdown.contains(event.target)) {
                this.dropdown.classList.remove("open");
            }
        });

        const scrollbarSizeObserver = new MutationObserver(() => {
            this.updateScrollbarSize();
        });

        scrollbarSizeObserver.observe(this.button, {
            childList: true,
            subtree: true,
            characterData: true
        });

        const scrollbarPositionObserver = new MutationObserver(() => {
            this.updateScrollbarPosition();
        });

        scrollbarPositionObserver.observe(this.button, {
            childList: true,
            subtree: true,
            characterData: true
        });

        this.button.addEventListener("scroll", () => {
            this.updateScrollbarPosition();
            this.showScrollbarWhileScrolling();
        });

        window.addEventListener("load", () => {
            this.updateScrollbarPosition();
        });
    }

    updateButtonText() {
        const selected = Array.from(this.checkboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.parentNode.textContent.trim());

        this.button.textContent = selected.length
            ? selected.join(", ")
            : this.defaultText;
    }

    updateScrollbarSize() {
        if (!this.customScrollbar) {
            return;
        }

        const totalScrollable = this.button.scrollWidth - this.button.clientWidth;

        if (totalScrollable > 0) {
            this.customScrollbar.style.width = `calc(100% - ${totalScrollable}px - 22px)`;
            this.customScrollbar.style.maxWidth = `calc(100% - ${totalScrollable}px - 22px)`;

            if (this.firstAppear) {
                this.firstAppear = false;
                this.customScrollbar.style.opacity = 1;

                setTimeout(() => {
                    this.customScrollbar.style.opacity = 0;
                }, 500);
            }
        } else if (totalScrollable === 0 && this.customScrollbar.style.width !== "0px") {
            this.customScrollbar.style.width = "0px";
            this.customScrollbar.style.transform = "translateX(0px)";
            this.firstAppear = true;
        }
    }

    updateScrollbarPosition() {
        if (!this.customScrollbar) {
            return;
        }

        const totalScrollable = this.button.scrollWidth - this.button.clientWidth;
        const translateX = totalScrollable > 220
            ? this.button.scrollLeft / (totalScrollable / 220)
            : this.button.scrollLeft;

        this.customScrollbar.style.transform = `translateX(${translateX}px)`;
    }

    showScrollbarWhileScrolling() {
        if (!this.customScrollbar) {
            return;
        }

        if (!this.isScrolling) {
            this.isScrolling = true;
            this.customScrollbar.style.opacity = 1;
        }

        clearTimeout(this.scrollTimeout);

        this.scrollTimeout = setTimeout(() => {
            this.isScrolling = false;
            this.customScrollbar.style.opacity = 0;
        }, 500);
    }
}

window.CheckboxDropdown = CheckboxDropdown;
