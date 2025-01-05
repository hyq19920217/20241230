// 创建一个数组来存储控制台日志
const consoleLogs = [];

// 重写 console.log 和 console.error
const originalLog = console.log;
const originalError = console.error;

console.log = function() {
    consoleLogs.push(`[LOG] ${Array.from(arguments).join(' ')}`);
    originalLog.apply(console, arguments);
};

console.error = function() {
    consoleLogs.push(`[ERROR] ${Array.from(arguments).join(' ')}`);
    originalError.apply(console, arguments);
};

// Excel 导入处理
document.getElementById('importForm').onsubmit = function(e) {
    e.preventDefault();
    // 清空之前的日志
    consoleLogs.length = 0;
    
    const formData = new FormData();
    const fileInput = document.getElementById('excelFile');
    const resultDiv = document.getElementById('importResult');
    
    // 显示上传开始
    resultDiv.innerHTML = '<div class="message">正在上传...</div>';
    
    // 记录每个步骤
    console.log('1. 开始处理文件上传');
    console.log('文件信息:', fileInput.files[0]);
    
    if (fileInput.files.length === 0) {
        alert('请选择文件');
        return;
    }

    formData.append('file', fileInput.files[0]);

    // 显示发送的数据
    console.log('2. 准备发送请求');
    console.log('FormData 内容:', Array.from(formData.entries()));

    fetch('/api/import_vocabulary.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('3. 收到服务器响应');
        console.log('响应状态:', response.status);
        console.log('响应头:', Array.from(response.headers.entries()));
        
        // 尝试读取响应内容
        return response.text().then(text => {
            console.log('4. 响应内容:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error(`解析响应失败: ${text}\n\n原始错误: ${e.message}`);
            }
        });
    })
    .then(data => {
        console.log('5. 处理响应数据:', data);
        resultDiv.innerHTML = `<p>${data.message}</p>`;
        resultDiv.className = data.status === 'success' ? 'message success' : 'message error';
        
        if (data.errors && data.errors.length > 0) {
            resultDiv.innerHTML += `
                <div class="error-list">
                    <h4>错误详情：</h4>
                    <ul>
                        ${data.errors.map(err => `<li>${err}</li>`).join('')}
                    </ul>
                </div>
            `;
        }
        
        if (data.status === 'success' || data.status === 'partial') {
            loadVocabularyList();
        }
    })
    .catch(error => {
        console.error('6. 发生错误:', error);
        resultDiv.innerHTML = 
            `<div class="message error">
                <p>导入失败：${error.message}</p>
                <details>
                    <summary>调试信息</summary>
                    <pre style="white-space: pre-wrap; word-break: break-all;">
                        Response Status: ${error.status || 'N/A'}
                        Error Message: ${error.message || 'N/A'}
                        Debug Info:
                        ${error.response ? JSON.stringify(error.response, null, 2) : 'No response data'}
                        
                        Server Response:
                        ${error.body || 'No response body'}
                        
                        Stack Trace:
                        ${error.stack || 'No stack trace available'}
                        
                        Console Log:
                        ${consoleLogs.join('\n')}
                    </pre>
                </details>
                <details>
                    <summary>PHP 错误日志</summary>
                    <div id="phpErrorLog">
                        <button onclick="fetchPhpErrorLog()">加载 PHP 错误日志</button>
                    </div>
                </details>
            </div>`;
        // 自动加载 PHP 错误日志
        fetchPhpErrorLog();
    });
};

// 手动添加处理
document.getElementById('addForm').onsubmit = function(e) {
    e.preventDefault();
    
    const word = document.getElementById('word').value;
    const partOfSpeech = document.getElementById('partOfSpeech').value;
    const meaning = document.getElementById('meaning').value;
    const example = document.getElementById('example').value;
    const exampleCn = document.getElementById('exampleCn').value;
    
    fetch('/api/add_vocabulary.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            word: word,
            partOfSpeech: partOfSpeech,
            meaning: meaning,
            example: example,
            exampleCn: exampleCn
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            throw new Error(data.message);
        }
        // 添加成功后刷新页面或显示成功消息
        alert('添加成功');
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('error').textContent = error.message;
    });
};

// 加载词汇列表
function loadVocabularyList() {
    const tbody = document.getElementById('vocabularyTableBody');
    const countSpan = document.getElementById('vocabularyCount');
    tbody.innerHTML = '<tr><td colspan="6">加载中...</td></tr>';
    
    fetch('/api/get_vocabulary.php')
        .then(response => response.json())
        .then(data => {
            console.log('Received data:', data);
            if (!Array.isArray(data)) {
                console.log('Data is not an array:', typeof data);
                throw new Error('Invalid data format');
            }
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6">暂无数据</td></tr>';
                countSpan.textContent = '0 个词汇';
                return;
            }
            countSpan.textContent = data.length + ' 个词汇';
            tbody.innerHTML = data.map(item => `
                <tr>
                    <td>
                        <input type="checkbox" class="vocab-select" data-id="${item.id}" style="display: none;">
                    </td>
                    <td>${item.word}</td>
                    <td>${item.part_of_speech}</td>
                    <td>${item.meaning}</td>
                    <td>${item.example}</td>
                    <td>${item.example_cn}</td>
                    <td class="action-buttons">
                        <button onclick="editVocabulary(${item.id})" class="edit-btn">编辑</button>
                        <button onclick="deleteVocabulary(${item.id})" class="delete-btn">删除</button>
                    </td>
                </tr>
            `).join('');
            
            // 如果正在批量删除模式，显示复选框
            if (document.querySelector('.batch-actions').style.display === 'block') {
                document.querySelectorAll('.vocab-select').forEach(checkbox => {
                    checkbox.style.display = 'inline-block';
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="6">加载失败</td></tr>';
        });
}

// 编辑词汇
function editVocabulary(id) {
    // TODO: 实现编辑功能
    alert('编辑功能开发中...');
}

// 删除词汇
function deleteVocabulary(id) {
    if (!confirm('确定要删除这个词汇吗？')) {
        return;
    }

    fetch(`/api/delete_vocabulary.php?id=${id}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadVocabularyList();
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('删除失败：' + error.message);
    });
}

// 添加获取 PHP 错误日志的函数
function fetchPhpErrorLog() {
    fetch('get_error_log.php', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(log => {
        document.getElementById('phpErrorLog').innerHTML = 
            `<pre style="max-height: 300px; overflow: auto;">${log}</pre>`;
    })
    .catch(error => {
        document.getElementById('phpErrorLog').innerHTML = 
            `<p class="error">无法获取错误日志: ${error.message}</p>`;
    });
}

// 搜索功能
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#vocabularyTableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
            // 高亮匹配文本
            if (searchTerm) {
                row.classList.add('highlight');
            } else {
                row.classList.remove('highlight');
            }
        } else {
            row.style.display = 'none';
        }
    });
});

// 回到顶部功能
const backToTop = document.getElementById('backToTop');

window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
        backToTop.classList.add('visible');
    } else {
        backToTop.classList.remove('visible');
    }
});

backToTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// 初始加载词汇列表
loadVocabularyList();

// 加载站内信
function loadMessages() {
    const messagesList = document.getElementById('messagesList');
    const unreadBadge = document.getElementById('unreadCount');
    
    fetch('/api/get_messages.php')
        .then(response => response.json())
        .then(messages => {
            const unreadCount = messages.filter(m => !m.is_read).length;
            
            if (unreadCount > 0) {
                unreadBadge.style.display = 'inline';
                unreadBadge.textContent = unreadCount;
            } else {
                unreadBadge.style.display = 'none';
            }
            
            messagesList.innerHTML = messages.map(message => `
                <div class="message-item ${message.is_read ? '' : 'unread'}" data-id="${message.id}">
                    <div class="message-header">
                        <div class="message-info">
                            <div class="message-time">${new Date(message.created_at).toLocaleString()}</div>
                            <div class="message-preview">${message.content.substring(0, 50)}${message.content.length > 50 ? '...' : ''}</div>
                        </div>
                        <div class="message-expand">
                            <span class="expand-icon">▼</span>
                        </div>
                    </div>
                    <div class="message-content">${message.content}</div>
                </div>
            `).join('');
            
            // 添加点击展开/收起事件
            document.querySelectorAll('.message-item').forEach(async item => {
                item.addEventListener('click', async () => {
                    item.classList.toggle('expanded');
                    // 如果是未读消息，点击时标记为已读
                    if (item.classList.contains('unread')) {
                        await markAsRead(item.dataset.id);
                        item.classList.remove('unread');
                    }
                });
            });
        })
        .catch(error => {
            console.error('Error:', error);
            messagesList.innerHTML = '<div class="error">加载消息失败</div>';
        });
}

// 标记消息为已读
async function markAsRead(id) {
    try {
        const response = await fetch('/api/mark_message_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        });
        const data = await response.json();
        if (data.status !== 'success') {
            throw new Error('Failed to mark as read');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// 添加标签切换功能
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.style.display = 'none';
        });
        
        const panelId = btn.dataset.tab + 'Panel';
        document.getElementById(panelId).style.display = 'block';
        
        // 根据不同的标签加载不同的内容
        if (btn.dataset.tab === 'messages') {
            loadMessages();
        } else if (btn.dataset.tab === 'vocabulary') {
            loadVocabularyList();
        } else if (btn.dataset.tab === 'articles') {
            loadArticles();
        }
    });
});

// 初始加载
const activeTab = document.querySelector('.tab-btn.active');
if (activeTab) {
    if (activeTab.dataset.tab === 'messages') {
        loadMessages();
    } else if (activeTab.dataset.tab === 'vocabulary') {
        loadVocabularyList();
    } else if (activeTab.dataset.tab === 'articles') {
        loadArticles();
    }
}

// 文章图片预览
document.getElementById('articleImage').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="预览图">`;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
});

// 提交文章
document.getElementById('articleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('title', document.getElementById('articleTitle').value);
    formData.append('content', document.getElementById('articleContent').value);
    formData.append('image', document.getElementById('articleImage').files[0]);

    try {
        const response = await fetch('/api/add_article.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (data.status === 'success') {
            alert('文章发布成功');
            document.getElementById('articleForm').reset();
            document.getElementById('imagePreview').innerHTML = '';
            loadArticles();
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        alert('发布失败：' + error.message);
    }
});

// 加载文章列表
async function loadArticles() {
    const articlesList = document.getElementById('articlesList');
    
    try {
        const response = await fetch('/api/get_articles.php');
        const articles = await response.json();
        
        articlesList.innerHTML = articles.map(article => `
            <div class="article-item">
                <div class="article-checkbox" style="display: none;">
                    <input type="checkbox" class="article-select" data-id="${article.id}">
                </div>
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
        // 先检查图片是否存在
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

// 在切换到文章面板时加载文章列表
document.querySelector('[data-tab="articles"]').addEventListener('click', loadArticles);

// 面板切换功能
function showPanel(panelName) {
    // 隐藏所有面板
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.style.display = 'none';
    });

    // 显示选中的面板
    const selectedPanel = document.getElementById(panelName + 'Panel');
    if (selectedPanel) {
        selectedPanel.style.display = 'block';
    }

    // 加载对应的内容
    if (panelName === 'messages') {
        loadMessages();
    } else if (panelName === 'vocabulary') {
        loadVocabularyList();
    } else if (panelName === 'articles') {
        loadArticles();
    }
}

// 全选功能
document.getElementById('selectAll').addEventListener('change', function(e) {
    const checkboxes = document.querySelectorAll('.vocab-select');
    checkboxes.forEach(checkbox => checkbox.checked = e.target.checked);
    updateBatchDeleteButton();
});

// 更新批量删除按钮状态
function updateBatchDeleteButton() {
    const selectedCount = document.querySelectorAll('.vocab-select:checked').length;
    document.getElementById('selectedCount').textContent = 
        selectedCount > 0 ? `已选择 ${selectedCount} 项` : '';
}

// 监听单个复选框的变化
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('vocab-select')) {
        updateBatchDeleteButton();
    }
});

// 开始批量删除模式
document.getElementById('startBatchDelete').addEventListener('click', function() {
    document.querySelector('.batch-actions').style.display = 'block';
    document.getElementById('startBatchDelete').style.display = 'none';
    // 显示所有复选框
    document.querySelectorAll('.vocab-select').forEach(checkbox => {
        checkbox.style.display = 'inline-block';
    });
});

// 取消批量删除模式
document.getElementById('cancelBatchDelete').addEventListener('click', function() {
    exitBatchDeleteMode();
});

function exitBatchDeleteMode() {
    document.querySelector('.batch-actions').style.display = 'none';
    document.getElementById('startBatchDelete').style.display = 'block';
    // 隐藏所有复选框并取消选中
    document.querySelectorAll('.vocab-select').forEach(checkbox => {
        checkbox.style.display = 'none';
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
}

// 更新批量删除按钮状态
function updateBatchDeleteButton() {
    const selectedCount = document.querySelectorAll('.vocab-select:checked').length;
    document.getElementById('selectedCount').textContent = 
        selectedCount > 0 ? `已选择 ${selectedCount} 项` : '';
}

// 批量删除功能
document.getElementById('confirmBatchDelete').addEventListener('click', function() {
    const selectedIds = Array.from(document.querySelectorAll('.vocab-select:checked'))
        .map(checkbox => checkbox.dataset.id);
    
    if (!confirm(`确定要删除选中的 ${selectedIds.length} 个词汇吗？`)) {
        return;
    }

    fetch('/api/batch_delete_vocabulary.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ids: selectedIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadVocabularyList();
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('批量删除失败：' + error.message);
    });
});

// 文章批量删除相关功能
document.getElementById('startArticleBatchDelete').addEventListener('click', function() {
    document.getElementById('articleBatchActions').style.display = 'block';
    this.style.display = 'none';
    document.querySelectorAll('.article-checkbox').forEach(checkbox => {
        checkbox.style.display = 'block';
    });
});

document.getElementById('cancelArticleBatchDelete').addEventListener('click', function() {
    exitArticleBatchDeleteMode();
});

function exitArticleBatchDeleteMode() {
    document.getElementById('articleBatchActions').style.display = 'none';
    document.getElementById('startArticleBatchDelete').style.display = 'block';
    document.querySelectorAll('.article-checkbox').forEach(checkbox => {
        checkbox.style.display = 'none';
    });
    document.querySelectorAll('.article-select').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAllArticles').checked = false;
}

document.getElementById('selectAllArticles').addEventListener('change', function(e) {
    document.querySelectorAll('.article-select').forEach(checkbox => {
        checkbox.checked = e.target.checked;
    });
    updateArticleBatchDeleteButton();
});

function updateArticleBatchDeleteButton() {
    const selectedCount = document.querySelectorAll('.article-select:checked').length;
    document.getElementById('selectedArticlesCount').textContent = 
        selectedCount > 0 ? `已选择 ${selectedCount} 项` : '';
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('article-select')) {
        updateArticleBatchDeleteButton();
    }
});

document.getElementById('confirmArticleBatchDelete').addEventListener('click', function() {
    const selectedIds = Array.from(document.querySelectorAll('.article-select:checked'))
        .map(checkbox => checkbox.dataset.id);
    
    if (!confirm(`确定要删除选中的 ${selectedIds.length} 篇文章吗？`)) {
        return;
    }

    fetch('/api/batch_delete_articles.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ids: selectedIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadArticles();
            exitArticleBatchDeleteMode();
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('批量删除失败：' + error.message);
    });
});
