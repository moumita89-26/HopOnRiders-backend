<nav class="nav nav-pills gap-2 mb-3" aria-label="Settlement type">
    <a class="nav-link {{ $activeTab === 'drivers' ? 'active' : '' }}" href="{{ route('admin.settlements.index') }}" @if($activeTab === 'drivers') aria-current="page" @endif>Driver Settlement</a>
    <a class="nav-link {{ $activeTab === 'customers' ? 'active' : '' }}" href="{{ route('admin.settlements.index', ['tab' => 'customers']) }}" @if($activeTab === 'customers') aria-current="page" @endif>Customer Refund</a>
</nav>
