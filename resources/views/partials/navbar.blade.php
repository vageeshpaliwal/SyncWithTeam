<nav class="navbar">

    <div>

        <h4>Dashboard</h4>

    </div>

    <div class="nav-right">

        <span>

           

        </span>

        <form
            action="{{ route('logout') }}"
            method="POST"
        >

            @csrf

            <button class="btn btn-danger btn-sm">

                Logout

            </button>

        </form>

    </div>

</nav>