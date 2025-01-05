const PAGE_SIZE = 10; // 每页显示的文章数量
let currentPage = 1;
let totalArticles = 0;

// 加载文章列表
async function loadArticles(page = 1) {
    const articlesList = document.getElementById('articlesList');
    const pagination = document.getElementById('pagination');
    
    try {
        const response = await fetch(`/api/get_articles.php?page=${page}&size=${PAGE_SIZE}`);
        const data = await response.json();
        totalArticles = data.total;
        
        // 更新文章总数显示
        document.getElementById('articleCount').textContent = `${totalArticles} 篇`;
        
        // 渲染文章列表
        articlesList.innerHTML = data.articles.map(article => `
            <div class="article-item" onclick="showArticleDetail(${JSON.stringify(article).replace(/"/g, '&quot;')})">
                <div class="article-title">${article.title}</div>
                <div class="article-meta">
                    发布时间：${new Date(article.created_at).toLocaleString()}
                </div>
            </div>
        `).join('');
        
        // 渲染分页
        const totalPages = Math.ceil(totalArticles / PAGE_SIZE);
        renderPagination(totalPages, currentPage);
        
    } catch (error) {
        console.error('Error:', error);
        articlesList.innerHTML = '<div class="error">加载文章失败</div>';
    }
}

// 渲染分页控件
function renderPagination(totalPages, currentPage) {
    const pagination = document.getElementById('pagination');
    let html = '';
    
    if (currentPage > 1) {
        html += `<button class="page-btn" onclick="changePage(${currentPage - 1})">上一页</button>`;
    }
    
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            html += `<button class="page-btn active">${i}</button>`;
        } else {
            html += `<button class="page-btn" onclick="changePage(${i})">${i}</button>`;
        }
    }
    
    if (currentPage < totalPages) {
        html += `<button class="page-btn" onclick="changePage(${currentPage + 1})">下一页</button>`;
    }
    
    pagination.innerHTML = html;
}

// 切换页面
function changePage(page) {
    currentPage = page;
    loadArticles(page);
    window.scrollTo(0, 0);
}

// 初始加载
loadArticles(1);

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