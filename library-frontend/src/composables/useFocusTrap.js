import { nextTick, onBeforeUnmount, watch } from "vue";

const FOCUSABLE =
  'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

/**
 * UI-04: tahan fokus di dalam dialog selama terbuka (Tab/Shift+Tab berputar),
 * lalu kembalikan fokus ke elemen pemicu saat ditutup.
 *
 * @param {import("vue").Ref<HTMLElement|null>} containerRef elemen dialog
 * @param {import("vue").Ref<boolean>} isOpen state buka/tutup milik pemanggil
 */
export function useFocusTrap(containerRef, isOpen) {
  let opener = null;

  const focusables = () =>
    [...(containerRef.value?.querySelectorAll(FOCUSABLE) || [])].filter(
      (el) => el.offsetParent !== null,
    );

  const onKeydown = (e) => {
    if (e.key !== "Tab") return;
    const nodes = focusables();
    if (!nodes.length) return;

    const first = nodes[0];
    const last = nodes[nodes.length - 1];

    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  };

  watch(
    isOpen,
    async (open) => {
      if (open) {
        opener = document.activeElement;
        document.addEventListener("keydown", onKeydown);
        await nextTick();
        // Hormati fokus yang sudah dipasang pemanggil (mis. field pertama form).
        if (!containerRef.value?.contains(document.activeElement)) {
          focusables()[0]?.focus();
        }
      } else {
        document.removeEventListener("keydown", onKeydown);
        opener?.focus?.();
        opener = null;
      }
    },
    { immediate: true },
  );

  onBeforeUnmount(() => document.removeEventListener("keydown", onKeydown));
}
