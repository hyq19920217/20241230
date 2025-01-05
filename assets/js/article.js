// 获取文章ID
const urlParams = new URLSearchParams(window.location.search);
const articleId = urlParams.get('id');

// 加载文章详情
async function loadArticle(id) {
    try {
        const response = await fetch(`/api/get_article.php?id=${id}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const article = data.article;
            
            // 更新页面标题
            document.title = article.title;
            
            // 填充文章内容
            document.getElementById('articleTitle').textContent = article.title;
            document.getElementById('articleMeta').textContent = 
                `发布时间：${new Date(article.created_at).toLocaleString()}`;
            
            if (article.image_path) {
                document.getElementById('articleImage').innerHTML = 
                    `<img src="/${article.image_path}" alt="${article.title}">`;
            }
            
            document.getElementById('articleContent').innerHTML = article.content;
            
            // 更新导航链接
            if (data.prev_id) {
                document.getElementById('prevArticle').href = `article.html?id=${data.prev_id}`;
            } else {
                document.getElementById('prevArticle').style.display = 'none';
            }
            
            if (data.next_id) {
                document.getElementById('nextArticle').href = `article.html?id=${data.next_id}`;
            } else {
                document.getElementById('nextArticle').style.display = 'none';
            }
        } else {
            throw new Error(data.message || '加载失败');
        }
    } catch (error) {
        console.error('Error:', error);
        document.body.innerHTML = '<div class="error">文章加载失败</div>';
    }
}

// 初始加载
if (articleId) {
    loadArticle(articleId);
} else {
    window.location.href = 'articles.html';
} 