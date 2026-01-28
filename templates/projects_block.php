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
        $phpFile = __DIR__ . '/../' . $phpLink;
        $htmlFile = __DIR__ . '/../' . $link;
        
        // Используем PHP версию если существует, иначе HTML
        if (file_exists($phpFile)) {
          $link = $phpLink;
        } elseif (file_exists($htmlFile)) {
          // Оставляем HTML если PHP не существует
          $link = $link;
        } else {
          // Если ни PHP ни HTML файл не существует, используем PHP версию
          $link = $phpLink;
        }
      } elseif (!preg_match('/\.(php|html)$/', $link)) {
        // Если ссылка без расширения, пробуем .php
        $phpFile = __DIR__ . '/../' . $link . '.php';
        $htmlFile = __DIR__ . '/../' . $link . '.html';
        
        if (file_exists($phpFile)) {
          $link = $link . '.php';
        } elseif (file_exists($htmlFile)) {
          $link = $link . '.html';
        } else {
          // Если файл не существует, добавляем .php
          $link = $link . '.php';
        }
      }
    }
    
    $link = htmlspecialchars($link);
  ?>
    <a class="card card-zoom" href="<?php echo $link; ?>">
      <figure class="bg-white h-44 flex items-center justify-center">
        <img src="<?php echo htmlspecialchars($project['logo_path']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="h-12 w-auto" />
      </figure>
      <div class="card-body">
        <h3 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h3>
        <p><?php echo htmlspecialchars($project['description']); ?></p>
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
          <?php 
          $tags = explode(',', $project['tags']);
          foreach ($tags as $tag): 
            $tag = trim($tag);
            if (!empty($tag)):
          ?>
            <span class="badge"><?php echo htmlspecialchars($tag); ?></span>
          <?php 
            endif;
          endforeach; 
          ?>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
<?php endif; ?>
