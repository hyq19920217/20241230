async function loadArticle() {
    const urlParams = new URLSearchParams(window.location.search);
    const articleId = urlParams.get('id');
    
    if (!articleId) {
        location.href = '/articles.html';
        return;
    }
    
    try {
        const response = await fetch(`/api/get_article.php?id=${articleId}`);
        const article = await response.json();
        
        document.title = article.title;
        
        const content = document.getElementById('articleContent');
        content.innerHTML = `
            ${article.image_path ? `<img src="/${article.image_path}" alt="${article.title}">` : ''}
            <h1>${article.title}</h1>
            <div class="article-meta">
                发布时间：${new Date(article.created_at).toLocaleString()}
            </div>
            <div class="article-body">
                ${article.content}
            </div>
        `;
        
        // 设置上一篇/下一篇按钮
        const prevBtn = document.getElementById('prevArticle');
        const nextBtn = document.getElementById('nextArticle');
        
        if (article.prev_id) {
            prevBtn.href = `/article.html?id=${article.prev_id}`;
            prevBtn.classList.remove('disabled');
        } else {
            prevBtn.classList.add('disabled');
        }
        
        if (article.next_id) {
            nextBtn.href = `/article.html?id=${article.next_id}`;
            nextBtn.classList.remove('disabled');
        } else {
            nextBtn.classList.add('disabled');
        }
        
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('articleContent').innerHTML = '<div class="error">加载文章失败</div>';
    }
}

// 加载文章详情
loadArticle(); 