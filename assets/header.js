// Единый компонент меню для всех страниц
(function() {
  // Определяем текущую страницу для активного пункта меню
  function getCurrentPage() {
    const path = window.location.pathname;
    let page = path.split('/').pop() || 'index.php';
    // Убираем параметры запроса если есть
    page = page.split('?')[0];
    // Если страница пустая, значит это index.php
    if (!page || page === '') {
      return 'index.php';
    }
    // Нормализуем расширения (.html -> .php для совместимости)
    if (page.endsWith('.html')) {
      page = page.replace('.html', '.php');
    }
    return page;
  }
  
  const currentPage = getCurrentPage();
  
  // Функция для определения активного пункта меню
  function isActive(href) {
    const page = href.split('/').pop().split('?')[0];
    // Нормализуем расширения для сравнения
    const normalizedHref = page.replace(/\.(html|php)$/, '');
    const normalizedCurrent = currentPage.replace(/\.(html|php)$/, '');
    return normalizedHref === normalizedCurrent ? 'active' : '';
  }
  
  function getAriaCurrent(href) {
    const page = href.split('/').pop().split('?')[0];
    // Нормализуем расширения для сравнения
    const normalizedHref = page.replace(/\.(html|php)$/, '');
    const normalizedCurrent = currentPage.replace(/\.(html|php)$/, '');
    return normalizedHref === normalizedCurrent ? 'aria-current="page"' : '';
  }
  
  // HTML меню
  const headerHTML = `
    <header class="sticky top-0 z-50 header-glass">
      <div class="navbar container mx-auto">
        <div class="navbar-start">
          <div class="dropdown">
            <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </div>
            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
              <li><a class="${isActive('index.php')}" href="index.php">Главная</a></li>
              <li><a class="${isActive('services.php')}" href="services.php">Услуги</a></li>
              <li><a class="${isActive('projects.php')}" href="projects.php">Проекты</a></li>
              <li><a class="${isActive('contacts.php')}" href="contacts.php">Контакты</a></li>
              <li><a class="${isActive('vacancies.php')}" href="vacancies.php">Вакансии</a></li>
              <li><a class="${isActive('blog.php')}" href="blog.php">Блог</a></li>
            </ul>
          </div>
          <a href="index.php" aria-label="ONZA.ME" class="flex items-center ml-5"><img src="assets/image/logo.svg" alt="ONZA.ME" class="logo-img" /></a>
        </div>
        <div class="navbar-center hidden lg:flex">
          <ul class="menu menu-horizontal px-1">
            <li><a class="${isActive('index.php')}" href="index.php" ${getAriaCurrent('index.php')}>Главная</a></li>
            <li><a class="${isActive('services.php')}" href="services.php" ${getAriaCurrent('services.php')}>Услуги</a></li>
            <li><a class="${isActive('projects.php')}" href="projects.php" ${getAriaCurrent('projects.php')}>Проекты</a></li>
            <li><a class="${isActive('contacts.php')}" href="contacts.php" ${getAriaCurrent('contacts.php')}>Контакты</a></li>
            <li><a class="${isActive('vacancies.php')}" href="vacancies.php" ${getAriaCurrent('vacancies.php')}>Вакансии</a></li>
            <li><a class="${isActive('blog.php')}" href="blog.php" ${getAriaCurrent('blog.php')}>Блог</a></li>
          </ul>
        </div>
      </div>
    </header>
  `;
  
  // Вставляем меню в начало body
  function initHeader() {
    const body = document.body;
    const header = body.querySelector('header');
    if (header) {
      header.outerHTML = headerHTML;
    } else {
      body.insertAdjacentHTML('afterbegin', headerHTML);
    }
  }
  
  // Запускаем сразу если DOM уже загружен, иначе ждем DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeader);
  } else {
    initHeader();
  }
})();
