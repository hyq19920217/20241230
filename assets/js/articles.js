// 加载文章列表
async function loadArticles() {
    const articlesList = document.getElementById('articlesList');
    
    try {
        const response = await fetch('/api/get_articles.php');
        const articles = await response.json();
        
        articlesList.innerHTML = articles.map(article => `
            <div class="article-item" onclick="showArticleDetail(${JSON.stringify(article).replace(/"/g, '&quot;')})">
                <div class="article-header">
                    <div class="article-info">
                        <div class="article-title">${article.title}</div>
                        <div class="article-time">
                            发布时间：${new Date(article.created_at).toLocaleString()}
                        </div>
                    </div>
                </div>
                <div class="article-preview">
                    ${article.content.substring(0, 100)}...
                </div>
                ${article.image_path ? `
                    <div class="image-preview">
                        <img src="/${article.image_path}" alt="${article.title}" onerror="this.parentElement.innerHTML='<div class=\'image-placeholder\'>图片加载失败</div>'">
                    </div>
                ` : ''}
            </div>
        `).join('');
    } catch (error) {
        console.error('Error:', error);
        articlesList.innerHTML = '<div class="error">加载文章失败</div>';
    }
}

// 初始加载
loadArticles(); 

// 显示文章详情
function showArticleDetail(article) {
    const modal = document.getElementById('articleModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalImage = document.getElementById('modalImage');
    const modalContent = document.getElementById('modalContent');
    const modalTime = document.getElementById('modalTime');
    
    modalTitle.textContent = article.title;
    modalContent.innerHTML = article.content;
    modalTime.textContent = `发布时间：${new Date(article.created_at).toLocaleString()}`;
    
    if (article.image_path) {
        const img = new Image();
        img.onload = function() {
            modalImage.innerHTML = `<img src="/${article.image_path}" alt="${article.title}">`;
        };
        img.onerror = function() {
            modalImage.innerHTML = '<div class="image-placeholder">图片加载失败</div>';
        };
        img.src = '/' + article.image_path;
    } else {
        modalImage.innerHTML = '<div class="image-placeholder">无图片</div>';
    }
    
    modal.style.display = 'block';
}

// 关闭模态框
document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('articleModal').style.display = 'none';
}); 