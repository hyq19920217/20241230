// 生成字母导航
const letters = '#ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
const nav = document.getElementById('letterNav');
let currentLetter = '';  // 默认不选中任何字母

letters.forEach(letter => {
    const btn = document.createElement('button');
    btn.className = `letter-btn ${letter === currentLetter ? 'active' : ''}`;
    btn.textContent = letter;
    btn.onclick = () => {
        document.querySelectorAll('.letter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentLetter = letter;
        // 滚动到对应区域
        const section = document.getElementById(`section-${letter}`);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
    };
    nav.appendChild(btn);
});

// 搜索功能
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const searchTerm = e.target.value.trim();
        if (searchTerm) {
            searchVocabulary(searchTerm);
        } else {
            loadVocabulary();
        }
    }, 300);
});

// 加载词汇列表
function loadVocabulary() {
    const list = document.getElementById('vocabularyList');
    const countSpan = document.getElementById('vocabularyCount');
    list.innerHTML = '<div class="loading">加载中...</div>';
    
    fetch('/api/get_vocabulary.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('网络请求失败');
            }
            return response.json();
        })
        .then(data => {
            if (data.length === 0) {
                vocabularyCount.textContent = '暂无数据';
                list.innerHTML = '<div class="loading">暂无数据</div>';
                return;
            }
            vocabularyCount.textContent = data.length + ' 个词汇';
            
            // 按字母分组
            const groups = {};
            data.forEach(item => {
                const firstChar = /^[A-Za-z]/.test(item.word) ? 
                    item.word[0].toUpperCase() : '#';
                if (!groups[firstChar]) {
                    groups[firstChar] = [];
                }
                groups[firstChar].push(item);
            });
            
            // 生成 HTML
            list.innerHTML = Object.entries(groups)
                .sort(([a], [b]) => a === '#' ? -1 : b === '#' ? 1 : a.localeCompare(b))
                .map(([letter, items]) => `
                    <div id="section-${letter}" class="letter-section">
                        <h2 class="letter-heading">${letter}</h2>
                        <div class="word-grid">
                            ${items.map(item => `
                                <div class="word-card">
                                    <div class="word-header">
                                        <div class="word">${item.word}</div>
                                        <div class="part-of-speech">${item.part_of_speech}</div>
                                    </div>
                                    <div class="meaning">${item.meaning}</div>
                                    <div class="example">${item.example}</div>
                                    <div class="example-cn">${item.example_cn}</div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `).join('');
        })
        .catch(error => {
            vocabularyCount.textContent = '连接数据库失败';
            list.innerHTML = '<div class="loading">加载失败</div>';
            console.error('加载词汇失败:', error);
        });
}

// 搜索词汇
function searchVocabulary(term) {
    const list = document.getElementById('vocabularyList');
    list.innerHTML = '<div class="loading">搜索中...</div>';
    
    fetch(`/api/search_vocabulary.php?term=${encodeURIComponent(term)}`)
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Search results:', data);
            if (data.length === 0) {
                list.innerHTML = '<div class="loading">未找到匹配的词汇</div>';
                return;
            }
            list.innerHTML = data.map(item => `
                <div class="word-card">
                    <div class="word-header">
                        <div class="word">${item.word}</div>
                        <div class="part-of-speech">${item.part_of_speech}</div>
                    </div>
                    <div class="meaning">${item.meaning}</div>
                    <div class="example">${item.example}</div>
                    <div class="example-cn">${item.example_cn}</div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Error:', error);
            list.innerHTML = `<div class="loading">搜索失败：${error.message}</div>`;
        });
}

// 默认加载所有词汇
loadVocabulary();

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

// 留言功能
const messageBtn = document.getElementById('messageBtn');
const messageModal = document.getElementById('messageModal');
const closeBtn = document.querySelector('.close');
const submitBtn = document.getElementById('submitMessage');

messageBtn.onclick = () => messageModal.classList.add('show');
closeBtn.onclick = () => messageModal.classList.remove('show');

submitBtn.onclick = () => {
    const content = document.getElementById('messageContent').value;
    if (!content.trim()) {
        alert('请输入留言内容');
        return;
    }
    
    fetch('/api/add_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `content=${encodeURIComponent(content)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('留言成功');
            messageModal.classList.remove('show');
            document.getElementById('messageContent').value = '';
        } else {
            throw new Error('提交失败');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('提交失败，请稍后重试');
    });
};

// 点击模态框外部关闭
window.onclick = (event) => {
    if (event.target === messageModal) {
        messageModal.classList.remove('show');
    }
};

// 在初始化函数中添加获取文章数量的代码
async function initialize() {
    try {
        // 获取词汇总数
        const vocabResponse = await fetch('/api/get_vocabulary_count.php');
        const vocabData = await vocabResponse.json();
        document.getElementById('vocabularyCount').textContent = vocabData.count + ' 个词汇';
        
        // 获取文章总数
        const articleResponse = await fetch('/api/get_articles.php?page=1&size=1');
        const articleData = await articleResponse.json();
        document.getElementById('articleCount').textContent = articleData.total + ' 篇';
        
        // 加载词汇列表
        loadVocabularyByLetter('A');
    } catch (error) {
        console.error('初始化失败:', error);
    }
}
