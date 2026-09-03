// SmartGov Market - Client-side Bilingual (EN/NE) Translation Engine
// File: assets/js/i18n.js

const translations = {
    en: {
        appName: "SmartGov Market",
        home: "Home",
        products: "Products",
        vendors: "Vendors",
        services: "Gov Services",
        complaints: "Complaints",
        map: "GIS Map",
        cart: "Cart",
        login: "Login",
        register: "Register",
        dashboard: "Dashboard",
        logout: "Logout",
        searchPlaceholder: "Search products, local vendors, or government services...",
        heroTitle: "Integrated E-Governance & E-Commerce Platform",
        heroSubtitle: "Empowering citizens, local businesses, and government administration through transparent digital services and verified commerce.",
        featuredProducts: "Featured Local Products",
        approvedVendors: "Government Approved Vendors",
        govServicesTitle: "Digital Government Services",
        vendorCTA: "Are you a local vendor? Register and apply for your digital business license online.",
        registerVendorBtn: "Register as Vendor",
        verifiedBadge: "Verified Business",
        footerTagline: "Connecting citizens, vendors, and government for transparent governance and thriving local economy.",
        quickLinks: "Quick Links",
        contactGov: "Government Support",
        copyright: "© 2026 SmartGov Market. All Rights Reserved.",
        licenseNo: "License No",
        status: "Status",
        price: "Price",
        stock: "Stock",
        addToCart: "Add to Cart",
        checkout: "Proceed to Checkout",
        orderNow: "Place Order",
        submitComplaint: "Submit Complaint",
        verifyLicense: "Verify Digital License"
    },
    ne: {
        appName: "स्मार्टगोभ मार्केट",
        home: "गृह पृष्ठ",
        products: "सामग्रीहरू",
        vendors: "व्यवसायीहरू",
        services: "सरकारी सेवाहरू",
        complaints: "गुनासोहरू",
        map: "जि.आइ.एस नक्सा",
        cart: "कारोबार झोला",
        login: "लगइन",
        register: "दर्ता गर्नुहोस्",
        dashboard: "ड्यासबोर्ड",
        logout: "लगआउट",
        searchPlaceholder: "सामग्री, स्थानीय व्यवसायी, वा सरकारी सेवा खोज्नुहोस्...",
        heroTitle: "एकीकृत ई-शासकीय तथा ई-कमर्स प्रणाली",
        heroSubtitle: "पारदर्शी डिजिटल सेवा र प्रमाणित व्यापार मार्फत नागरिक, स्थानीय व्यवसायी र सरकारी प्रशासनलाई सशक्त बनाउँदै।",
        featuredProducts: "प्रमुख स्थानीय उत्पादनहरू",
        approvedVendors: "सरकारी स्वीकृत व्यवसायीहरू",
        govServicesTitle: "डिजिटल सरकारी सेवाहरू",
        vendorCTA: "के तपाईं स्थानीय व्यवसायी हुनुहुन्छ? अनलाइन दर्ता गरी डिजिटल व्यापार इजाजतपत्र लिनुहोस्।",
        registerVendorBtn: "व्यवसायी दर्ता गर्नुहोस्",
        verifiedBadge: "प्रमाणित व्यवसाय",
        footerTagline: "पारदर्शी शासन र समृद्ध स्थानीय अर्थतन्त्रका लागि नागरिक, व्यवसायी र सरकारको साझा मञ्च।",
        quickLinks: "द्रुत लिङ्कहरू",
        contactGov: "सरकारी सहयोग",
        copyright: "© २०२६ स्मार्टगोभ मार्केट। सर्वाधिकार सुरक्षित।",
        licenseNo: "इजाजतपत्र नं.",
        status: "स्थिति",
        price: "मूल्य",
        stock: "मौजदात",
        addToCart: "झोलामा थप्नुहोस्",
        checkout: "भुक्तानीमा अगाडि बढ्नुहोस्",
        orderNow: "अर्डर पेश गर्नुहोस्",
        submitComplaint: "गुनासो दर्ता गर्नुहोस्",
        verifyLicense: "इजाजतपत्र प्रमाणीकरण"
    }
};

let currentLang = localStorage.getItem('smartgov_lang') || 'en';

function setLanguage(lang) {
    if (!translations[lang]) return;
    currentLang = lang;
    localStorage.setItem('smartgov_lang', lang);
    applyTranslations();
}

function applyTranslations() {
    const dict = translations[currentLang];
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (dict[key]) {
            if (el.tagName === 'INPUT' && el.getAttribute('placeholder')) {
                el.setAttribute('placeholder', dict[key]);
            } else {
                el.textContent = dict[key];
            }
        }
    });

    const langToggleBtn = document.getElementById('lang-toggle-btn');
    if (langToggleBtn) {
        langToggleBtn.textContent = currentLang === 'en' ? 'नेपाली' : 'English';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    applyTranslations();
    const langToggleBtn = document.getElementById('lang-toggle-btn');
    if (langToggleBtn) {
        langToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            setLanguage(currentLang === 'en' ? 'ne' : 'en');
        });
    }
});
