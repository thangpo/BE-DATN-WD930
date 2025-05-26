<style>
.icon-container {
    position: relative;
    display: inline-flex; /* Chuyển đổi từ inline-block sang inline-flex để hỗ trợ căn giữa */
    align-items: center; /* Căn giữa icon và label theo trục dọc */
    justify-content: center;
    color: #ffffff;
    font-size: 16px;
    text-decoration: none;
}

.icon-container i {
    margin-right: 8px; /* Khoảng cách giữa icon và text */
}

.badge-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    font-size: 16px; /* Tăng/giảm kích thước để cân đối */
    font-weight: 400;
    color: #ffffff;
}

.badge-count {
    position: absolute;
    top: -11px;
    right: -7px;
    background-color: #ffd43b; /* Tô màu nổi bật */
    color: #333;
    font-size: 11px;
    font-weight: bold;
    border-radius: 50%;
    padding: 3px 6px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.navbar-nav .nav-item {
    margin-left: -5px; /* Khoảng cách giữa các mục trong navbar */
}

.nav-item a {
    font-size: 16px; /* Đồng nhất kích thước text */
}
.dropdown-submenu {
  position: relative;
}

.dropdown-submenu .dropdown-menu {
  top: 0;
  left: 100%;
  margin-left: 0.1rem;
  margin-top: -0.5rem;
  display: none;
}

.dropdown-submenu:hover .dropdown-menu {
  display: block;
}
#user{
    margin-bottom: 8px
}
#dn{
    margin-bottom: 3px
}
</style>
<!-- Navbar Start -->
<div class="container-fluid bg-dark mb-30">
    <div class="row px-xl-5">
      <div class="col-lg-2 d-none d-lg-block">
        <a
          class="btn d-flex align-items-center justify-content-between bg-primary w-100"
          data-toggle="collapse"
          href="#navbar-vertical"
          style="height: 65px; padding: 0 30px"
        >
          <h6 class="text-dark m-0">
            <i class="fa fa-bars mr-2"></i>Chuyên khoa
          </h6>
          <i class="fa fa-angle-down text-dark"></i>
        </a>
        <nav
          class="collapse position-absolute navbar navbar-vertical navbar-light align-items-start p-0 bg-light"
          id="navbar-vertical"
          style="width: calc(100% - 30px); z-index: 999"
        >
          <div class="navbar-nav w-100">
                @foreach ($spe as $s)
                <a href="{{ route('appoinment.booKingCare', $s->id) }}" class="dropdown-item">{{ $s->name }}</a>
                @endforeach
          </div>
        </nav>
      </div>
      <div class="col-lg-10">
        <nav
          class="navbar navbar-expand-lg bg-dark navbar-dark py-3 py-lg-0 px-0"
        >
          <a href="{{ route('home') }}" class="text-decoration-none d-block d-lg-none">
            <span class="h4 text-uppercase text-dark bg-light px-2"
              >Instinct</span
            >
            <span class="h1 text-uppercase text-light bg-primary px-2 ml-n1"
              >Pharmacy</span
            >
          </a>
          <button
            type="button"
            class="navbar-toggler"
            data-toggle="collapse"
            data-target="#navbarCollapse"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div
            class="collapse navbar-collapse justify-content-between"
            id="navbarCollapse"
          >
            <div class="navbar-nav mr-auto py-0">
              <a href="{{ route('home') }}" class="nav-item nav-link active">Trang Chủ</a>
              <div class="nav-item dropdown">
                <a href="{{ route('products') }}" class="nav-link dropdown-toggle" data-toggle="dropdown">
                  Tất Cả Sản Phẩm
                </a>
                <div class="dropdown-menu bg-light rounded-0 border-0 m-0">
                  @foreach ($categories as $category)
                    <div class="dropdown-submenu">
                      <a href="{{ route('productsByCategoryId', $category->id) }}" class="dropdown-item">
                        {{ $category->name }}
                      </a>
                      <div class="dropdown-menu">
                        @foreach ($category->products as $product)
                          <a href="{{ route('productDetail', $product->id) }}" class="dropdown-item">
                            {{ $product->name }}
                          </a>
                        @endforeach
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
              <a href="{{ route('appoinment.index') }}" class="nav-item nav-link">Đặt Lịch Khám</a>
              <div class="nav-item dropdown">
                <a
                  href="#"
                  class="nav-link dropdown-toggle"
                  data-toggle="dropdown"
                  >Về chúng tôi<i class="fa fa-angle-down mt-1"></i
                ></a>
                <div class="dropdown-menu bg-primary rounded-0 border-0 m-0">
                    <a href="{{ route('about') }}" class="dropdown-item">Giới Thiệu</a>
                   <a href="{{ route('appoinment.doctorDetailsall') }}" class="dropdown-item">Đội ngũ bác sỹ</a>
                </div>
              </div>
              <a href="{{route('blog.index')}}" class="nav-item nav-link">Tin tức</a>
              <a href="{{ route('contact') }}" class="nav-item nav-link">Liên hệ</a>
            </div>
            <div class="navbar-nav ml-auto py-0 d-none d-lg-block">
                @if(Auth::check() && (Auth::user()->role == 'User'))
                <a href="{{ route('cart.listCart') }}" class="btn icon-container px-0 ml-3">
                    <i class="fas fa-shopping-cart text-primary"></i>
                    <span class="badge-label">
                        <span class="badge-count" id="count">{{ \App\Models\Cart::where('user_id', Auth::id())->withCount('items')->first()->items_count ?? 0 }}</span>
                        Giỏ hàng
                    </span>
                </a>
                @endif
                @if(Auth::check() && (Auth::user()->role == 'User'))
                <a href="{{ route('orders.index') }}" class="btn icon-container px-0 ">
                    {{-- <i class="fas fa-file-invoice-dollar text-primary"></i> --}}
                    <span class="badge-label">
                        <span class="badge-count">{{ $orderCount }}</span>
                        Kiểm tra đơn mua
                    </span>
                </a>
                @endif
                {{-- <a href="" class="btn icon-container px-0 ml-3">
                    <i class="fas fa-bell" style="color: #ffd43b"></i>
                    <span class="badge-label">
                        <span class="badge-count">0</span>
                        Thông báo
                    </span>
                </a> --}}
                @if(Auth::check() && (Auth::user()->role == 'User'))
                @php
                // Lấy số lần đặt khám của người dùng đã đăng nhập
                $appointmentCount = Auth::user()->appoinment()->count();
                @endphp
                <a class="btn icon-container px-0" href="{{ route('appoinment.appointmentHistory', $user = Auth::user()->id) }}">
                    <span class="badge-label">
                  <span class="badge-count"> {{ $appointmentCount }}</span>
                  Lịch sử đặt khám
                    </span>
                </a>
                @endif

                    @if(Auth::check() && (Auth::user()->role == 'Doctor'))
                    @php
                    // Lấy bác sĩ của người dùng
                    $doctor = Auth::user()->doctor;

                    // Lấy số lịch khám, kiểm tra nếu $doctor không null
                    $appointmentCount1 = $doctor ? $doctor->appoinment()->count() : 0;
                   @endphp
                    <a class="btn icon-container px-0" href="{{ route('appoinment.physicianManagement', $user = Auth::user()->id) }}">
                        <span class="badge-label">
                      <span class="badge-count">{{ $appointmentCount1 }} </span>
                      Quản lý lịch khám
                        </span>
                    </a>
                    @endif

                @if(auth()->check())
                    <a href="{{ route('account') }}" class="btn icon-container px-0 ">
                        <i class="fas fa-user" style="color: #ffd43b"></i>
                        <span class="badge-label">Tài khoản</span>
                    </a>
                @else
                    <a href="{{ route('viewLogin') }}" class="btn icon-container px-0 ">
                        <i class="fas fa-user" id="user" style="color: #ffd43b"></i>
                        <span class="badge-label" id="dn">Đăng nhập/Đăng ký</span>
                    </a>
                @endif
            </div>

          </div>
        </nav>
      </div>
    </div>
  </div>
  <!-- Navbar End -->
