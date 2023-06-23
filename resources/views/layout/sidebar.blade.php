<nav class="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand">
            My<span>Journal</span>
        </a>
        <div class="sidebar-toggler not-active">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav">
            <li class="nav-item nav-category">Main</li>
            <li class="nav-item {{ active_class(['/']) }}">
                <a href="{{ url('/') }}" class="nav-link">
                    <i class="link-icon" data-feather="book"></i>
                    <span class="link-title">Tickets</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['settlements']) }}">
                <a href="{{ url('settlements') }}" class="nav-link">
                    <i class="link-icon" data-feather="check-circle"></i>
                    <span class="link-title">Settlements</span>
                </a>
            </li>
            <li class="nav-item nav-category">Others</li>
            <li class="nav-item {{ active_class(['day-sheet']) }}">
                <a href="{{ url('day-sheet') }}" class="nav-link">
                    <i class="link-icon" data-feather="calendar"></i>
                    <span class="link-title">Day Sheet</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
