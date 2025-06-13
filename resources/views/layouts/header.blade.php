<style>
.top-bar {
    width: 100%;
    height: 60px;
    background-color: #fff;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 0 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border-bottom: 1px solid #ebedf2;
    z-index: 1000;
}
.logo-section {
    display: flex;
    align-items: center;
}
.logo-section img {
    height: 36px;
    margin-right: 12px;
}
.logo-bold { font-weight: bold; color: #222; }
.logo-red { color: #e74c3c; font-weight: bold; }
</style>

<div class="top-bar">
    <div class="logo-section">
        <img src="{{ asset('images/mcmc-logo.png') }}" alt="MCMC Logo">
        <h2 style="margin:0;">
            <span class="logo-bold">MYSE</span><span class="logo-red">BENARNYA</span>
        </h2>
    </div>
</div>
