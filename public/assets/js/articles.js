async function loadArticles() {
    const articlesList = document.getElementById('articlesList');
    
    try {
        const response = await fetch('/api/get_articles.php');
        const articles = await response.json();
        
        articlesList.innerHTML = articles.map(article => `
            <div class="article-card" onclick="location.href='/article.html?id=${article.id}'">
                ${article.image_path ? `
                    <img src="/${article.image_path}" alt="${article.title}" class="article-image">
                ` : ''}
                <div class="article-info">
                    <div class="article-title">${article.title}</div>
                    <div class="article-date">${new Date(article.created_at).toLocaleDateString()}</div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error:', error);
        articlesList.innerHTML = '<div class="error">加载文章失败</div>';
    }
}

// 初始加载文章列表
loadArticles(); 