// Drives the Discord-style settings layout: category click expands its
// sub-items and switches which category's scroll-pane is visible;
// sub-item click smooth-scrolls to its <section> inside that ONE
// scrollable pane (never swaps to a separate panel); and a scroll
// listener keeps the matching sub-item highlighted as the user scrolls
// past each section, the same way Discord's does.
(function () {
    function activateGroup(root, groupKey) {
        root.querySelectorAll('[data-group]').forEach(g => {
            const isTarget = g.dataset.group === groupKey;
            g.classList.toggle('active', isTarget);
            g.querySelector('[data-group-subs]')?.classList.toggle('hidden', !isTarget);
        });
        root.querySelectorAll('[data-category-content]').forEach(c => {
            c.classList.toggle('hidden', c.dataset.categoryContent !== groupKey);
        });
    }

    function setActiveSublink(root, groupKey, sectionId) {
        root.querySelectorAll('.settings-v2-sublink').forEach(link => {
            link.classList.toggle(
                'active',
                link.dataset.parentGroup === groupKey && link.dataset.scrollTarget === sectionId
            );
        });
    }

    function initScrollspy(root, groupKey) {
        const scrollarea = root.querySelector(`[data-category-content="${groupKey}"]`);
        if (!scrollarea) return;

        const sections = Array.from(scrollarea.querySelectorAll('.settings-v2-section'));
        if (sections.length === 0) return;

        const observer = new IntersectionObserver((entries) => {
            // Pick the entry closest to the top of the viewport that's
            // still intersecting, so the highlight tracks scroll position
            // the way Discord's does rather than jumping erratically.
            const visible = entries.filter(e => e.isIntersecting);
            if (visible.length === 0) return;
            visible.sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
            setActiveSublink(root, groupKey, visible[0].target.id);
        }, {
            root: scrollarea,
            rootMargin: '0px 0px -70% 0px',
            threshold: 0,
        });

        sections.forEach(s => observer.observe(s));
        scrollarea.dataset.scrollspyInit = 'true';
    }

    function init(root) {
        root.querySelectorAll('[data-group-toggle]').forEach(btn => {
            btn.addEventListener('click', () => {
                const key = btn.dataset.groupToggle;
                activateGroup(root, key);
                const scrollarea = root.querySelector(`[data-category-content="${key}"]`);
                if (scrollarea && scrollarea.dataset.scrollspyInit !== 'true') {
                    initScrollspy(root, key);
                }
                const firstSection = scrollarea?.querySelector('.settings-v2-section');
                if (firstSection) setActiveSublink(root, key, firstSection.id);
            });
        });

        root.querySelectorAll('[data-scroll-target]').forEach(link => {
            link.addEventListener('click', () => {
                const groupKey = link.dataset.parentGroup;
                const targetId = link.dataset.scrollTarget;
                const scrollarea = root.querySelector(`[data-category-content="${groupKey}"]`);
                const targetEl = document.getElementById(targetId);
                if (scrollarea && targetEl) {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                setActiveSublink(root, groupKey, targetId);
            });
        });

        // Init scrollspy for whichever category starts active.
        const activeGroup = root.querySelector('.settings-v2-group.active');
        if (activeGroup) {
            const key = activeGroup.dataset.group;
            initScrollspy(root, key);
            const firstSub = activeGroup.querySelector('.settings-v2-sublink');
            if (firstSub) firstSub.classList.add('active');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-settings-v2]').forEach(init);
    });
})();
