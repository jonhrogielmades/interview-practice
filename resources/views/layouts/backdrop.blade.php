{{-- <div
    x-show="$store.sidebar.isMobileOpen"
    @click="$store.sidebar.toggleMobileOpen()"
    class="fixed inset-0 bg-gray-900/50 z-[9999] xl:hidden"
>
sidebarToggle ? 'block xl:hidden' : 'hidden'
</div> --}}

<div
  x-cloak
  x-show="$store.sidebar.isMobileOpen"
  x-transition.opacity
  @click="$store.sidebar.setMobileOpen(false)"
  :class="$store.sidebar.isMobileOpen ? 'block xl:hidden' : 'hidden'"
  class="fixed inset-0 z-[100000] bg-gray-900/50 backdrop-blur-[2px]"
  aria-hidden="true"
></div>
