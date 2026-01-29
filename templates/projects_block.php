<?php
/**
 * Блок проектов для главной страницы
 * Используется в index.php
 */
if (empty($projects)): ?>
  <div class="col-span-full text-center py-12">
    <p class="text-gray-500">Проекты пока не добавлены.</p>
  </div>
<?php else: ?>
  <?php foreach ($projects as $project): 
    // Обрабатываем ссылку: конвертируем .html в .php
    $link = trim($project['link']);
    
    // Если ссылка не внешняя (не начинается с http/https)
    if (!preg_match('/^https?:\/\//', $link)) {
      // Если ссылка заканчивается на .html, пробуем .php версию
      if (preg_match('/\.html$/', $link)) {
        $phpLink = preg_replace('/\.html$/', '.php', $link);
        $phpFile = __DIR__ . '/../projects/' . $phpLink;
        $htmlFile = __DIR__ . '/../projects/' . $link;
        
        // Используем PHP версию если существует, иначе HTML
        if (file_exists($phpFile)) {
          $link = '/projects/' . $phpLink;
        } elseif (file_exists($htmlFile)) {
          // Оставляем HTML если PHP не существует
          $link = '/projects/' . $link;
        } else {
          // Если ни PHP ни HTML файл не существует, используем PHP версию
          $link = '/projects/' . $phpLink;
        }
      } elseif (!preg_match('/\.(php|html)$/', $link)) {
        // Если ссылка без расширения, пробуем .php
        $phpFile = __DIR__ . '/../projects/' . $link . '.php';
        $htmlFile = __DIR__ . '/../projects/' . $link . '.html';
        
        if (file_exists($phpFile)) {
          $link = '/projects/' . $link . '.php';
        } elseif (file_exists($htmlFile)) {
          $link = '/projects/' . $link . '.html';
        } else {
          // Если файл не существует, добавляем .php
          $link = '/projects/' . $link . '.php';
        }
      } else {
        // Если ссылка уже с расширением .php или .html, добавляем абсолютный путь
        $link = '/projects/' . $link;
      }
    }
    
    $link = htmlspecialchars($link);
  ?>
    <a class="card card-zoom" href="<?php echo $link; ?>">
      <figure class="bg-white h-44 flex items-center justify-center">
        <img src="<?php echo htmlspecialchars($project['logo_path']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="h-12 w-auto object-contain" />
      </figure>
      <div class="card-body flex flex-col">
        <h3 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h3>
        <?php if (!empty($project['subtitle'])): ?>
          <p class="text-sm mb-4"><?php echo htmlspecialchars($project['subtitle']); ?></p>
        <?php else: ?>
          <p class="text-sm mb-4"><?php echo htmlspecialchars($project['description']); ?></p>
        <?php endif; ?>
        <div class="mt-auto flex flex-wrap gap-2 text-xs">
          <?php 
          $tags = explode(',', $project['tags']);
          foreach ($tags as $tag): 
            $tag = trim($tag);
            if (!empty($tag)):
              // Проверяем, является ли тег URL
              $isUrl = preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/', $tag);
              if ($isUrl):
                // Добавляем https:// если отсутствует
                $url = preg_match('/^https?:\/\//', $tag) ? $tag : 'https://' . $tag;
          ?>
            <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener noreferrer" class="badge badge-outline hover:badge-primary"><?php echo htmlspecialchars($tag); ?></a>
          <?php else: ?>
            <span class="badge"><?php echo htmlspecialchars($tag); ?></span>
          <?php 
              endif;
            endif;
          endforeach; 
          ?>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
<?php endif; ?>
