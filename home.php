<?php
require_once 'config.php';

// Get statistics
$total_members = $conn->query("SELECT COUNT(*) as total FROM members")->fetch_assoc()['total'];
$total_trophies = $conn->query("SELECT COUNT(*) as total FROM trophies")->fetch_assoc()['total'];

// Get trophies with photos
$trophies_list = $conn->query("SELECT * FROM trophies ORDER BY id DESC LIMIT 6");

// Check if user already registered as member
$is_member = false;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $check = $conn->query("SELECT * FROM members WHERE user_id = '$user_id'");
    $is_member = $check->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Volley Club – PORPPAD Surabaya</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
    
    <style>
/* ===== MODERN VARIABLES ===== */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --primary-dark: #5a67d8;
    --primary-light: #7c3aed;
    --gold: #fbbf24;
    --gold-light: #fcd34d;
    --text-dark: #1a202c;
    --text-light: #718096;
    --bg-light: #f7fafc;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
    --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
    --shadow-xl: 0 20px 60px rgba(0,0,0,0.15);
    --radius-sm: 12px;
    --radius-md: 16px;
    --radius-lg: 24px;
}

* {
    -webkit-tap-highlight-color: transparent;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
    touch-action: manipulation;
    -webkit-text-size-adjust: 100%;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    padding-top: 80px;
    overflow-x: hidden;
    background: #ffffff;
    color: var(--text-dark);
    line-height: 1.7;
}

/* ===== MODERN NAVBAR ===== */
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.05);
    z-index: 1000;
    padding: 0.75rem 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.navbar.scrolled {
    padding: 0.5rem 0;
    box-shadow: var(--shadow-md);
}

.logo-img {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-sm);
    object-fit: cover;
    transition: transform 0.3s ease;
}

.navbar-brand:hover .logo-img {
    transform: scale(1.05) rotate(2deg);
}

.brand-title {
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 700;
    font-size: 1.25rem;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1.2;
}

.brand-sub {
    font-size: 0.8rem;
    color: var(--text-light);
    font-weight: 500;
    letter-spacing: 0.5px;
}

.nav-link {
    font-size: 0.95rem;
    padding: 0.6rem 1rem !important;
    color: var(--text-dark);
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: var(--radius-sm);
    position: relative;
}

.nav-link::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%) scaleX(0);
    width: 80%;
    height: 2px;
    background: var(--primary-gradient);
    transition: transform 0.3s ease;
}

.nav-link:hover {
    color: var(--primary-dark);
    background: rgba(102, 126, 234, 0.05);
}

.nav-link:hover::before {
    transform: translateX(-50%) scaleX(1);
}

.nav-link.active {
    color: white !important;
    background: var(--primary-gradient);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.nav-link.active::before {
    display: none;
}

.btn-rounded {
    border-radius: 100px;
    padding: 0.6rem 1.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
}

.btn-gold {
    background: linear-gradient(135deg, var(--gold), var(--gold-light));
    color: var(--text-dark);
    box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
}

.btn-gold:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(251, 191, 36, 0.4);
    color: var(--text-dark);
}

/* ===== MODERN HERO ===== */
.hero-section {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    position: relative;
    padding: 4rem 1rem;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.hero-section h1:first-of-type {
    font-family: 'Inter', sans-serif;
    font-size: clamp(1rem, 3vw, 1.5rem);
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin-bottom: 1rem;
    color: rgba(255, 255, 255, 0.9);
    text-shadow: 0 2px 20px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 1;
}

.orbitron-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: clamp(3rem, 12vw, 8rem);
    font-weight: 800;
    letter-spacing: 0.02em;
    background: linear-gradient(135deg, #ffffff 0%, #fbbf24 50%, #ffffff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 2rem;
    position: relative;
    z-index: 1;
    text-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.9; }
}

.hero-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-top: 3rem;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
    position: relative;
    z-index: 1;
}

.hero-stats .stat {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    padding: 2rem 1rem;
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-align: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.hero-stats .stat:hover {
    transform: translateY(-10px) scale(1.05);
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.hero-stats .stat h3 {
    font-size: clamp(2.5rem, 6vw, 4rem);
    margin: 0 0 0.5rem 0;
    color: #fff;
    font-weight: 900;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}

.hero-stats small {
    font-size: clamp(0.9rem, 2vw, 1.1rem);
    color: rgba(255, 255, 255, 0.95);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

/* ===== MODERN SECTIONS ===== */
section {
    padding: clamp(4rem, 8vw, 7rem) 0;
    position: relative;
}

.section-title {
    text-align: center;
    margin-bottom: 4rem;
}

.section-title h3 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 1rem;
    position: relative;
    display: inline-block;
}

.section-title h3::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: var(--primary-gradient);
    border-radius: 2px;
}

.section-title p {
    font-size: clamp(1rem, 2vw, 1.2rem);
    color: var(--text-light);
    max-width: 600px;
    margin: 0 auto;
}

/* ===== VISI MISI CARDS ===== */
.visi-misi-card {
    background: white;
    padding: 3rem;
    border-radius: var(--radius-lg);
    border: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: var(--shadow-md);
    height: 100%;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.visi-misi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background: var(--primary-gradient);
}

.visi-misi-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-xl);
}

.vm-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    filter: drop-shadow(0 4px 10px rgba(102, 126, 234, 0.3));
}

.visi-misi-card h4 {
    font-family: 'Space Grotesk', sans-serif;
    color: var(--text-dark);
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.visi-misi-card p {
    font-size: 1.1rem;
    line-height: 1.9;
    color: var(--text-light);
}

.vm-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.vm-list li {
    padding: 1rem 0;
    padding-left: 2.5rem;
    position: relative;
    font-size: 1.05rem;
    line-height: 1.7;
    color: var(--text-light);
    transition: all 0.3s ease;
}

.vm-list li:hover {
    color: var(--text-dark);
    transform: translateX(5px);
}

.vm-list li::before {
    content: "✓";
    position: absolute;
    left: 0;
    width: 32px;
    height: 32px;
    background: var(--primary-gradient);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* ===== FACILITY CARDS ===== */
.facility-card {
    background: white;
    padding: 2.5rem 2rem;
    border-radius: var(--radius-lg);
    border: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: var(--shadow-md);
    text-align: center;
    height: 100%;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.facility-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--primary-gradient);
    opacity: 0;
    transition: opacity 0.4s ease;
}

.facility-card:hover::before {
    opacity: 0.05;
}

.facility-card:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: var(--shadow-xl);
}

.facility-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    transition: all 0.4s ease;
    filter: drop-shadow(0 4px 10px rgba(102, 126, 234, 0.2));
}

.facility-card:hover .facility-icon {
    transform: scale(1.2) rotate(-5deg);
}

.facility-card h5 {
    font-family: 'Space Grotesk', sans-serif;
    color: var(--text-dark);
    font-size: 1.35rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.facility-card p {
    font-size: 1rem;
    color: var(--text-light);
    line-height: 1.7;
}

/* ===== TROPHY CARDS ===== */
.trophy-card {
    border-radius: var(--radius-lg);
    overflow: hidden;
    background: white;
    border: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: var(--shadow-md);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
}

.trophy-card:hover {
    transform: translateY(-12px) scale(1.02);
    box-shadow: var(--shadow-xl);
}

.trophy-img {
    width: 100%;
    height: 240px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 5rem;
    color: white;
    position: relative;
    overflow: hidden;
}

.trophy-img::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.trophy-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.trophy-content {
    padding: 1.75rem;
}

.trophy-badge {
    display: inline-block;
    padding: 0.4rem 1rem;
    border-radius: 100px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.75rem;
}

.badge-prestasi { background: #fef3c7; color: #92400e; }
.badge-turnamen { background: #dbeafe; color: #1e40af; }
.badge-kejuaraan { background: #ede9fe; color: #5b21b6; }
.badge-penghargaan { background: #fce7f3; color: #9f1239; }

.trophy-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.15rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

/* ===== TEAM CARDS ===== */
#team .row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

#team .row > div:nth-child(1) {
    flex: 0 0 100%;
    display: flex;
    justify-content: center;
}

#team .row > div:nth-child(1) .team-card {
    width: 100%;
    max-width: 450px;
}

#team .row > div:nth-child(2),
#team .row > div:nth-child(3) {
    flex: 0 0 calc(50% - 1rem);
    max-width: 450px;
}

.team-card {
    background: white;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.team-card:hover {
    transform: translateY(-12px) scale(1.02);
    box-shadow: var(--shadow-xl);
}

.team-photo {
    width: 100%;
    height: 500px;
    overflow: hidden;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    position: relative;
}

.team-photo::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 60%, rgba(0, 0, 0, 0.2));
    z-index: 1;
}

.team-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.team-card:hover .team-photo img {
    transform: scale(1.08);
}

.team-card .card-body {
    padding: 2rem;
    text-align: center;
    background: var(--primary-gradient);
}

.team-card h5 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: white;
}

.team-card small {
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
}

.team-card h3 {
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.85);
    font-weight: 500;
    margin-top: 0.75rem;
}

/* ===== GALLERY ===== */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.gallery-item {
    border-radius: var(--radius-lg);
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: var(--shadow-md);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    aspect-ratio: 1;
    cursor: pointer;
    position: relative;
}

.gallery-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 60%, rgba(0, 0, 0, 0.3));
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: 1;
}

.gallery-item:hover::before {
    opacity: 1;
}

.gallery-item:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow-xl);
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.gallery-item:hover img {
    transform: scale(1.1);
}

/* ===== SCHEDULE TABLE ===== */
.schedule-table {
    font-size: 1rem;
    border: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: var(--shadow-md);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.schedule-table th,
.schedule-table td {
    padding: 1.25rem;
    vertical-align: middle;
}

.schedule-table thead {
    background: var(--primary-gradient);
    color: white;
}

.schedule-table thead th {
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.9rem;
}

.schedule-table tbody tr {
    transition: all 0.3s ease;
}

.schedule-table tbody tr:hover {
    background: rgba(102, 126, 234, 0.05);
    transform: scale(1.01);
}

/* ===== FOOTER ===== */
footer {
    background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
    color: #cbd5e0;
    padding: 4rem 0 2rem 0;
    font-size: 0.95rem;
}

.social-media {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.social-media a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    text-decoration: none;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 1.3rem;
    backdrop-filter: blur(10px);
}

.social-media a:hover {
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.social-instagram:hover {
    background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
}
.social-youtube:hover { background: #FF0000; }
.social-tiktok:hover { background: #000000; }
.social-whatsapp:hover { background: #25D366; }
.social-twitter:hover { background: #1DA1F2; }

/* ===== RESPONSIVE ===== */
@media (max-width: 991px) {
    .hero-stats {
        gap: 1rem;
    }
    
    .hero-stats .stat {
        padding: 1.5rem 0.75rem;
    }
}

@media (max-width: 768px) {
    body {
        padding-top: 70px;
    }
    
    .navbar {
        padding: 0.5rem 0;
    }
    
    .hero-section {
        padding: 3rem 1rem;
    }
    
    .visi-misi-card,
    .facility-card {
        margin-bottom: 1.5rem;
    }
    
    #team .row > div:nth-child(1),
    #team .row > div:nth-child(2) {
        flex: 0 0 calc(50% - 0.5rem);
        max-width: calc(50% - 0.5rem);
    }
    
    #team .row > div:nth-child(3) {
        flex: 0 0 calc(50% - 0.5rem);
        max-width: calc(50% - 0.5rem);
    }
    
    .team-photo {
        height: 320px;
    }
    
    .team-card .card-body {
        padding: 1.25rem;
    }
    
    .team-card h5 {
        font-size: 1.1rem;
    }
    
    .team-card small {
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .gallery-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .team-photo {
        height: 280px;
    }
    
    .team-card h5 {
        font-size: 1rem;
    }
    
    .team-card small {
        font-size: 0.85rem;
    }
    
    .team-card h3 {
        font-size: 0.9rem;
    }
}

/* ===== ANIMATIONS ===== */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ===== MODAL MODERN ===== */
.modal-content {
    border-radius: var(--radius-lg);
    border: none;
    box-shadow: var(--shadow-xl);
    overflow: hidden;
}

.modal-header {
    background: var(--primary-gradient);
    color: white;
    padding: 1.5rem 2rem;
    border: none;
}

.modal-header .modal-title {
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 600;
    font-size: 1.5rem;
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    padding: 1.5rem 2rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.form-label {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-control,
.form-select {
    border-radius: var(--radius-sm);
    border: 1px solid rgba(0, 0, 0, 0.1);
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--primary-dark);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.alert {
    border-radius: var(--radius-sm);
    border: none;
    padding: 1rem 1.25rem;
}

.alert-info {
    background: rgba(102, 126, 234, 0.1);
    color: var(--primary-dark);
}

/* ===== BUTTONS ENHANCED ===== */
.btn {
    font-weight: 600;
    border-radius: var(--radius-sm);
    padding: 0.75rem 1.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
}

.btn-primary {
    background: var(--primary-gradient);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4);
}

.btn-outline-light {
    border: 2px solid white;
    color: white;
    background: transparent;
}

.btn-outline-light:hover {
    background: white;
    color: var(--primary-dark);
    transform: translateY(-2px);
}

.btn-outline-danger {
    border: 2px solid #ef4444;
    color: #ef4444;
}

.btn-outline-danger:hover {
    background: #ef4444;
    color: white;
    transform: translateY(-2px);
}

/* ===== LOADING SPINNER ===== */
.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* ===== CTA SECTION ===== */
.bg-primary {
    background: var(--primary-gradient) !important;
}
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="home.php">
                <img src="assets/img/logo/logo.png" alt="logo" class="me-2 logo-img" onerror="this.style.display='none'" />
                <div>
                    <div class="brand-title">Volley Club</div>
                    <small class="brand-sub">PORPPAD</small>
                </div>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link active" href="#home">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="#visi-misi">Visi & Misi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#prestasi">Prestasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#team">Pelatih</a></li>
                    <li class="nav-item"><a class="nav-link" href="timpa.php">Tim</a></li>
                    <li class="nav-item"><a class="nav-link" href="#galeri">Galeri</a></li>
                    <li class="nav-item"><a class="nav-link" href="#jadwal">Jadwal</a></li>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item ms-2">
                                <a href="admin/dashboard.php" class="btn btn-primary btn-rounded">
                                    <i class="fa fa-dashboard"></i> Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item ms-1">
                            <a href="logout.php" class="btn btn-outline-danger btn-rounded">
                                <i class="fa fa-sign-out me-1"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-2">
                            <a href="index.php" class="btn btn-primary btn-rounded">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <header id="home" class="hero-section text-center text-light">
        <div class="container" data-aos="fade-up">
            <h1>CLUB BOLA VOLLY SURABAYA</h1>
            <h1 class="orbitron-title">PORPPAD</h1>
            
            <?php if (isLoggedIn()): ?>
                <p class="lead" style="font-size: 1.25rem; color: rgba(255,255,255,0.95);">Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username']) ?></strong>! 🎉</p>
            <?php else: ?>
                <p class="lead" style="font-size: 1.25rem; color: rgba(255,255,255,0.95);">Bergabunglah dengan klub voli terbaik di Surabaya</p>
            <?php endif; ?>
            
            <div class="d-flex justify-content-center gap-3 mb-3 flex-wrap">
                <a href="#about" class="btn btn-outline-light btn-rounded" style="padding: 0.875rem 2rem;">Pelajari Lebih</a>
                
                <?php if (isLoggedIn()): ?>
                    <?php if ($is_member): ?>
                        <a href="timpa.php" class="btn btn-gold btn-rounded" style="padding: 0.875rem 2rem;">
                            <i class="fa fa-users me-1"></i> Lihat Tim
                        </a>
                    <?php else: ?>
                        <button class="btn btn-gold btn-rounded" style="padding: 0.875rem 2rem;" data-bs-toggle="modal" data-bs-target="#daftarModal">
                            <i class="fa fa-user-plus me-1"></i> Daftar Anggota
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="btn btn-gold btn-rounded" style="padding: 0.875rem 2rem;">Login / Gabung</a>
                <?php endif; ?>
            </div>
            
            <div class="hero-stats">
                <div class="stat" data-aos="zoom-in" data-aos-delay="100">
                    <h3><?= $total_members ?></h3>
                    <small>Anggota</small>
                </div>
                <div class="stat" data-aos="zoom-in" data-aos-delay="200">
                    <h3><?= $total_trophies ?></h3>
                    <small>Prestasi</small>
                </div>
                <div class="stat" data-aos="zoom-in" data-aos-delay="300">
                    <h3>2025</h3>
                    <small>Tahun</small>
                </div>
            </div>
        </div>
    </header>

    <!-- ABOUT -->
    <section id="about" style="background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%);">
        <div class="container" data-aos="fade-up">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <img src="assets/img/logo/logo.png" class="img-fluid rounded shadow-lg" alt="latihan" style="max-width: 400px; display: block; margin: 0 auto; border-radius: var(--radius-lg) !important;" />
                </div>
                <div class="col-lg-6">
                    <h2 class="mb-4" style="font-family: 'Space Grotesk', sans-serif; font-size: 2.5rem; font-weight: 700;">Tentang Klub</h2>
                    <p class="text-muted" style="font-size: 1.15rem; line-height: 1.9;">Wadah pengembangan bakat voli dengan fokus pelatihan berkala, turnamen, dan pembentukan karakter atlet muda.</p>
                    <ul class="list-unstyled mt-4" style="font-size: 1.05rem;">
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fa fa-check-circle me-3" style="color: var(--primary-dark); font-size: 1.5rem;"></i>
                            <span>Latihan 5x/minggu dengan pelatih bersertifikat</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fa fa-check-circle me-3" style="color: var(--primary-dark); font-size: 1.5rem;"></i>
                            <span>Pendaftaran & manajemen digital</span>
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="fa fa-check-circle me-3" style="color: var(--primary-dark); font-size: 1.5rem;"></i>
                            <span>Program pembinaan usia junior</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- VISI & MISI -->
    <section id="visi-misi" style="background: white;">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>🎯 Visi & Misi</h3>
                <p>Arah dan tujuan Club Bola Voli PORPPAD</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="visi-misi-card">
                        <div class="vm-icon">🔭</div>
                        <h4>Visi</h4>
                        <p>Menjadi klub bola voli terkemuka di Surabaya yang menghasilkan atlet berprestasi, berkarakter, dan profesional dengan mengedepankan sportivitas, disiplin, dan kerja sama tim.</p>
                    </div>
                </div>

                <div class="col-lg-6" data-aos="fade-left">
                    <div class="visi-misi-card">
                        <div class="vm-icon">🎯</div>
                        <h4>Misi</h4>
                        <ul class="vm-list">
                            <li>Menyelenggarakan pelatihan rutin dengan standar profesional</li>
                            <li>Mengikuti berbagai kompetisi tingkat lokal hingga nasional</li>
                            <li>Membentuk karakter atlet yang disiplin dan sportif</li>
                            <li>Membangun komunitas pecinta bola voli yang solid</li>
                            <li>Mengembangkan bakat atlet muda sejak dini</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FASILITAS -->
    <section id="fasilitas" style="background: linear-gradient(to bottom, #f8fafc 0%, #e3f2fd 100%);">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>⚡ Fasilitas & Program</h3>
                <p>Apa yang kami tawarkan untuk anggota</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="0">
                    <div class="facility-card">
                        <div class="facility-icon">🏐</div>
                        <h5>Peralatan Lengkap</h5>
                        <p>Bola, net, dan equipment standar internasional untuk latihan optimal</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="facility-card">
                        <div class="facility-icon">👨‍🏫</div>
                        <h5>Pelatih Bersertifikat</h5>
                        <p>Tim pelatih berpengalaman dengan sertifikasi nasional dan internasional</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="facility-card">
                        <div class="facility-icon">📅</div>
                        <h5>Jadwal Teratur</h5>
                        <p>Latihan 5x seminggu dengan program terstruktur dan terukur</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
                    <div class="facility-card">
                        <div class="facility-icon">🏆</div>
                        <h5>Program Kompetisi</h5>
                        <p>Kesempatan mengikuti turnamen lokal hingga nasional</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="400">
                    <div class="facility-card">
                        <div class="facility-icon">💪</div>
                        <h5>Physical Training</h5>
                        <p>Program latihan fisik dan conditioning untuk performa maksimal</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="500">
                    <div class="facility-card">
                        <div class="facility-icon">👥</div>
                        <h5>Komunitas Solid</h5>
                        <p>Bergabung dengan keluarga besar pecinta voli yang suportif</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PRESTASI -->
    <section id="prestasi" style="background: white;">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>🏆 Prestasi & Turnamen</h3>
                <p>Pencapaian gemilang klub kami</p>
            </div>
            
            <div class="row g-4">
                <?php if ($trophies_list->num_rows > 0):
                    $delay = 0;
                    while ($trophy = $trophies_list->fetch_assoc()): ?>
                        <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                            <div class="trophy-card">
                                <div class="trophy-img">
                                    <?php if ($trophy['foto'] && file_exists("uploads/trophies/" . $trophy['foto'])): ?>
                                        <img src="uploads/trophies/<?= htmlspecialchars($trophy['foto']) ?>" alt="<?= htmlspecialchars($trophy['judul']) ?>" />
                                    <?php else: ?>
                                        🏆
                                    <?php endif; ?>
                                </div>
                                <div class="trophy-content">
                                    <span class="trophy-badge badge-<?= strtolower($trophy['badge']) ?>">
                                        <?= htmlspecialchars($trophy['badge']) ?>
                                    </span>
                                    <div class="trophy-title"><?= htmlspecialchars($trophy['judul']) ?></div>
                                    <div class="trophy-desc" style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.5rem;"><?= htmlspecialchars(substr($trophy['deskripsi'], 0, 60)) . '...' ?></div>
                                    <div class="trophy-date" style="font-size: 0.85rem; color: var(--text-light);">📅 <?= htmlspecialchars($trophy['tanggal']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        $delay += 50;
                    endwhile;
                else: ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-muted" style="font-size: 1.1rem;">Belum ada prestasi yang ditambahkan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- PELATIH -->
    <section id="team" style="background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%);">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>👨‍🏫 Pelatih PORPPAD</h3>
                <p>Tim pelatih profesional kami</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <div class="col-6 col-md-4" data-aos="zoom-in">
                    <div class="team-card">
                        <div class="team-photo">
                            <img src="assets/img/pelatih/pelatih1.png" alt="Om Agus" onerror="this.style.display='none'" />
                        </div>
                        <div class="card-body text-center py-4">
                            <h5>Sapta Agus PH</h5>
                            <small>Ketua & Pelatih</small>
                            <h3>📞 +62 877-8816-3653</h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="team-card">
                        <div class="team-photo">
                            <img src="assets/img/pelatih/pelatih2.png" alt="Om Nasir" onerror="this.style.display='none'" />
                        </div>
                        <div class="card-body text-center py-4">
                            <h5>Moch Nasir</h5>
                            <small>Pelatih</small>
                            <h3>📞 +62 856-5534-6527</h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="team-card">
                        <div class="team-photo">
                            <img src="assets/img/pelatih/pelatih3.png" alt="Anton" onerror="this.style.display='none'" />
                        </div>
                        <div class="card-body text-center py-4">
                            <h5>Taufik Efendi</h5>
                            <small>Pelatih</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- GALLERY -->
    <section id="galeri" style="background: white;">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>📸 Galeri</h3>
                <p>Momen latihan & pertandingan</p>
            </div>
            
            <div class="gallery-grid" data-aos="fade-up" id="galleryGrid"></div>
            
            <div class="text-center mt-5" id="loadMoreContainer" style="display: none;">
                <button class="btn btn-primary btn-rounded" id="loadMoreBtn" onclick="loadMorePhotos()" style="padding: 0.875rem 2.5rem; font-size: 1rem;">
                    <i class="fa fa-images me-2"></i>Lihat Lebih Banyak
                </button>
            </div>
            
            <div class="text-center mt-5" id="loadingIndicator" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </section>

    <!-- JADWAL -->
    <section id="jadwal" style="background: linear-gradient(to bottom, #f8fafc 0%, #e3f2fd 100%);">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h3>📅 Jadwal Latihan</h3>
                <p>Jadwal rutin latihan mingguan</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-10" data-aos="fade-up">
                    <div class="table-responsive">
                        <table class="schedule-table table table-bordered mb-0 text-center">
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Waktu</th>
                                    <th>Materi</th>
                                    <th>Tempat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Selasa</strong></td>
                                    <td>19:00-22:00</td>
                                    <td>Fisik & Teknik</td>
                                    <td>Rumah-Pucang</td>
                                </tr>
                                <tr>
                                    <td><strong>Rabu</strong></td>
                                    <td>18:00-22:00</td>
                                    <td>Drill & Game</td>
                                    <td>GOR FASHA</td>
                                </tr>
                                <tr>
                                    <td><strong>Kamis</strong></td>
                                    <td>19:00-22:00</td>
                                    <td>Fisik & Teknik</td>
                                    <td>Rumah-Pucang</td>
                                </tr>
                                <tr>
                                    <td><strong>Jumat</strong></td>
                                    <td>18:00-22:00</td>
                                    <td>Drill & Game</td>
                                    <td>GOR FASHA</td>
                                </tr>
                                <tr>
                                    <td><strong>Sabtu</strong></td>
                                    <td>14:30-Selesai</td>
                                    <td>Drill & Game</td>
                                    <td>Lap.Al-Irsyad</td>
                                </tr>
                                <tr>
                                    <td><strong>Minggu</strong></td>
                                    <td>Tentatif</td>
                                    <td>Fisik & Teknik</td>
                                    <td>Rumah-Pucang</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-5 bg-primary text-light" style="background: var(--primary-gradient) !important;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8 text-center text-md-start" data-aos="fade-right">
                    <h4 style="font-family: 'Space Grotesk', sans-serif; font-size: 2rem; margin-bottom: 0.75rem; font-weight: 700;">Gabung dengan kami!</h4>
                    <p class="mb-0" style="font-size: 1.15rem;">Hubungi admin untuk info latihan dan event</p>
                </div>
                <div class="col-md-4 text-center text-md-end mt-4 mt-md-0" data-aos="fade-left">
                    <?php if (isLoggedIn() && !$is_member): ?>
                        <button class="btn btn-outline-light btn-rounded" style="padding: 0.875rem 2rem;" data-bs-toggle="modal" data-bs-target="#daftarModal">
                            <i class="fa fa-user-plus me-1"></i> Daftar Anggota
                        </button>
                    <?php else: ?>
                        <a href="<?= isLoggedIn() ? 'timpa.php' : 'login.php' ?>" class="btn btn-outline-light btn-rounded" style="padding: 0.875rem 2rem;">
                            <i class="fa fa-<?= isLoggedIn() ? 'users' : 'user-plus' ?> me-1"></i> <?= isLoggedIn() ? 'Lihat Tim' : 'Login / Gabung' ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h5 class="fw-bold mb-3" style="font-family: 'Space Grotesk', sans-serif;">Volley Club PORPPAD</h5>
                    <p class="mb-2" style="font-size: 0.95rem; line-height: 1.8; color: #cbd5e0;">
                        GOR FASHA • Surabaya<br>
                        📞 Telp Cs: 082141186468<br>
                        📧 Email: clubvolley@gmail.com
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="fw-bold mb-3">Ikuti Kami</h6>
                    <div class="social-media">
                        <a href="https://instagram.com" target="_blank" class="social-instagram" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://youtube.com" target="_blank" class="social-youtube" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://tiktok.com" target="_blank" class="social-tiktok" title="TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="https://wa.me/6281234567890" target="_blank" class="social-whatsapp" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://twitter.com" target="_blank" class="social-twitter" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center">
                <small style="font-size: 0.9rem; color: #a0aec0;">
                    © 2025 Volley Club PORPPAD. All rights reserved.
                </small>
            </div>
        </div>
    </footer>

    <!-- MODAL DAFTAR ANGGOTA -->
    <div class="modal fade" id="daftarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-user-plus me-2"></i>Form Pendaftaran Anggota
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <form id="formDaftar" action="proses_daftar.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle me-2"></i>
                            <strong>Informasi:</strong> Setelah submit, data Anda diterima oleh admin dan anda akan dihubungi.
                        </div>

                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold">Foto Profil <span class="text-danger">*</span></label>
                            <div style="background: #f8f9fa; padding: 2rem; border-radius: var(--radius-md); border: 2px dashed #dee2e6;">
                                <div class="mb-3">
                                    <img id="photoPreview" 
                                         src="assets/img/default-profile.jpg" 
                                         alt="Preview" 
                                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 4px 20px rgba(0,0,0,0.15);"
                                         onerror="this.src='https://via.placeholder.com/150'">
                                </div>
                                
                                <input type="file" 
                                       name="foto" 
                                       id="fotoInput" 
                                       class="form-control" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif"
                                       required
                                       style="max-width: 400px; margin: 0 auto;">
                                
                                <small class="text-muted d-block mt-2">
                                    <i class="fa fa-info-circle"></i> Format: JPG, PNG, GIF (Max: 2MB)
                                </small>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" placeholder="Nama lengkap sesuai KTP" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                                <input type="text" name="tempat_lahir" class="form-control" placeholder="Surabaya" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_lahir" class="form-control" id="tanggal_lahir_modal" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Umur <span class="text-danger">*</span></label>
                                <input type="number" name="umur" class="form-control" id="umur_modal" min="10" max="60" placeholder="25" required readonly>
                                <small class="text-muted">Otomatis dari tanggal lahir</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="">Pilih Gender</option>
                                    <option value="Putra">Putra</option>
                                    <option value="Putri">Putri</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Posisi <span class="text-danger">*</span></label>
                                <select name="posisi" class="form-select" required>
                                    <option value="">Pilih Posisi</option>
                                    <option value="Spiker">Spiker</option>
                                    <option value="Setter">Setter</option>
                                    <option value="Libero">Libero</option>
                                    <option value="Blocker">Blocker</option>
                                    <option value="Server">Server</option>
                                    <option value="All-rounder">All-rounder</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">No. WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" name="wa" class="form-control" placeholder="08xxxxxxxxxx" pattern="[0-9]{10,13}" required>
                                <small class="text-muted">Format: 08xxxxxxxxxx</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Alamat <span class="text-danger">*</span></label>
                                <input type="text" name="alamat" class="form-control" placeholder="Kota, Kecamatan" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Alasan Ingin Bergabung <span class="text-danger">*</span></label>
                                <textarea name="alasan" class="form-control" rows="3" placeholder="Ceritakan alasan Anda..." required></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Pengalaman Bermain Voli</label>
                                <textarea name="pengalaman" class="form-control" rows="2" placeholder="Ceritakan pengalaman Anda (opsional)"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Riwayat Cedera (Opsional)</label>
                                <textarea name="riwayat_cedera" class="form-control" rows="2" placeholder="Riwayat cedera yang perlu kami ketahui?"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fa fa-times me-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane me-1"></i> Kirim Pendaftaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({ 
            once: true, 
            duration: 800,
            easing: 'ease-out-cubic'
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    const offsetTop = target.offsetTop - 70;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Auto calculate age
        document.getElementById('tanggal_lahir_modal')?.addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            document.getElementById('umur_modal').value = age;
        });

        // Preview Photo
        document.getElementById('fotoInput')?.addEventListener('change', function() {
            const file = this.files[0];
            const preview = document.getElementById('photoPreview');
            
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('⚠️ Ukuran foto terlalu besar! Maksimal 2MB');
                    this.value = '';
                    preview.src = 'assets/img/default-profile.jpg';
                    return;
                }
                
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('⚠️ Format tidak valid! Gunakan JPG, PNG, atau GIF');
                    this.value = '';
                    preview.src = 'assets/img/default-profile.jpg';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Gallery System
        let currentPhotoIndex = 5;
        let allPhotos = [];
        let isLoading = false;

        async function fetchGalleryPhotos() {
            try {
                const response = await fetch('get_gallery.php');
                const data = await response.json();
                
                if (data.success) {
                    allPhotos = data.photos;
                    renderInitialPhotos();
                    
                    if (allPhotos.length > 5) {
                        document.getElementById('loadMoreContainer').style.display = 'block';
                    }
                }
            } catch (error) {
                console.error('Error fetching photos:', error);
            }
        }

        function renderInitialPhotos() {
            const grid = document.getElementById('galleryGrid');
            grid.innerHTML = '';
            
            const initialPhotos = allPhotos.slice(0, 5);
            initialPhotos.forEach(photo => {
                grid.appendChild(createPhotoElement(photo));
            });
        }

        function loadMorePhotos() {
            if (isLoading) return;
            
            isLoading = true;
            const loadingIndicator = document.getElementById('loadingIndicator');
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            
            loadingIndicator.style.display = 'block';
            loadMoreBtn.style.display = 'none';
            
            setTimeout(() => {
                const grid = document.getElementById('galleryGrid');
                const nextPhotos = allPhotos.slice(currentPhotoIndex, currentPhotoIndex + 5);
                
                nextPhotos.forEach((photo, index) => {
                    setTimeout(() => {
                        const photoElement = createPhotoElement(photo);
                        photoElement.style.opacity = '0';
                        grid.appendChild(photoElement);
                        
                        setTimeout(() => {
                            photoElement.style.transition = 'opacity 0.5s';
                            photoElement.style.opacity = '1';
                        }, 50);
                    }, index * 100);
                });
                
                currentPhotoIndex += 5;
                
                loadingIndicator.style.display = 'none';
                
                if (currentPhotoIndex < allPhotos.length) {
                    loadMoreBtn.style.display = 'inline-block';
                } else {
                    document.getElementById('loadMoreContainer').innerHTML = 
                        '<p class="text-muted" style="font-size: 1.1rem;">Semua foto sudah ditampilkan 🎉</p>';
                }
                
                isLoading = false;
            }, 500);
        }

        function createPhotoElement(photo) {
            const div = document.createElement('div');
            div.className = 'gallery-item';
            div.innerHTML = `
                <img src="uploads/gallery/${photo.filename}" 
                     alt="${photo.caption || 'Gallery'}" 
                     onerror="this.src='https://via.placeholder.com/300'" 
                     onclick="openPhotoModal('${photo.filename}', '${photo.caption || ''}')" />
            `;
            return div;
        }

        function openPhotoModal(filename, caption) {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.95);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                padding: 2rem;
                backdrop-filter: blur(10px);
            `;
            
            modal.innerHTML = `
                <div style="max-width: 90%; max-height: 90%; text-align: center;">
                    <img src="uploads/gallery/${filename}" 
                         style="max-width: 100%; max-height: 80vh; border-radius: var(--radius-lg); box-shadow: 0 20px 60px rgba(0,0,0,0.5);" 
                         alt="${caption}" />
                    ${caption ? `<p style="color: white; margin-top: 1.5rem; font-size: 1.2rem; font-weight: 500;">${caption}</p>` : ''}
                    <button onclick="this.parentElement.parentElement.remove()" 
                            style="margin-top: 1.5rem; padding: 0.875rem 2rem; background: white; border: none; border-radius: 100px; cursor: pointer; font-size: 1rem; font-weight: 600; transition: all 0.3s;">
                        Tutup
                    </button>
                </div>
            `;
            
            modal.onclick = (e) => {
                if (e.target === modal) modal.remove();
            };
            
            document.body.appendChild(modal);
        }

        // Navbar active section highlight
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.nav-link');
            
            function highlightNav() {
                let current = '';
                const scrollY = window.pageYOffset;
                
                sections.forEach(section => {
                    const sectionHeight = section.offsetHeight;
                    const sectionTop = section.offsetTop - 100;
                    const sectionId = section.getAttribute('id');
                    
                    if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                        current = sectionId;
                    }
                });
                
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${current}`) {
                        link.classList.add('active');
                    }
                });
            }
            
            window.addEventListener('scroll', highlightNav);
            window.addEventListener('load', highlightNav);
            
            // Initialize gallery
            fetchGalleryPhotos();
        });

        // Handle click manual
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.nav-link').forEach(item => {
                    item.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>