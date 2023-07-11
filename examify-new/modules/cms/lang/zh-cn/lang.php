<?php return [
  'cms_object' => [
    'invalid_file' => '非法文件名： :name。文件名中只能包括字母或数字下划线、破折号和点。 正确的文件名: page.htm, page, subdirectory/page',
    'invalid_file_inspector' => '文件名无效。文件名只能包含字母数字符号、下划线、破折号和点。一些正确文件名的例子: page.htm, page, subdirectory/page',
    'invalid_property' => '无法设置属性 \':name\' ',
    'file_already_exists' => '文件 \':name\' 已存在.',
    'error_saving' => '保存文件 \':name\' 错误。请检查写权限。',
    'error_creating_directory' => '创建文件夹 :name 错误。请检查写权限。',
    'invalid_file_extension' => '非法文件扩展名: :invalid. 允许的扩展名: :allowed.',
    'error_deleting' => '删除模板文件 \':name\' 错误. 请检查写权限.',
    'delete_success' => '成功删除模板: :count.',
    'file_name_required' => '需要文件名字段.',
    'safe_mode_enabled' => '已启用安全模式.',
  ],
  'dashboard' => [
    'active_theme' => [
      'widget_title_default' => '网站',
      'online' => '在线',
      'maintenance' => '维护',
      'manage_themes' => '管理主题',
      'customize_theme' => '自定义主题',
    ],
  ],
  'theme' => [
    'active' => [
      'not_set' => '未设置活动主题',
      'not_found' => '无法找到活动主题',
      'is_locked' => '主题 \':theme\' 被锁定，无法使用。 请复制此主题或创建一个子主题。 ',
    ],
    'edit' => [
      'not_set' => '未设置编辑主题.',
      'not_found' => '无法找到编辑主题',
      'not_match' => '您所尝试访问的对象不属于正在编辑的主题。请重载页面。',
    ],
    'setting_edit_theme' => '更改编辑主题',
    'edit_theme_changed' => '编辑主题已更改',
  ],
  'page' => [
    'not_found_name' => '无法找到页面 \':name\'',
    'not_found' => [
      'label' => '无法找到页面',
      'help' => '无法找到所请求的页面',
    ],
    'custom_error' => [
      'label' => '页面错误',
      'help' => '很抱歉，发生了错误导致页面不能显示.',
    ],
    'menu_label' => '页面',
    'unsaved_label' => '未保存页面',
    'no_list_records' => '找不到页面',
    'new' => '新页面',
    'invalid_url' => '不合法的URL格式. URL可以以正斜杠开头, 包含数字, 拉丁字母和下面的字符: ._-[]:?|/+*^$',
    'delete_confirm_multiple' => '真的想要删除选择的页面吗?',
    'delete_confirm_single' => '真的想要删除这个页面吗?',
    'no_layout' => '-- 无布局 --',
    'title' => '页面标题',
    'url' => '页面地址',
    'url_required' => '页面 URL 是必需的。',
    'file_name' => '页面文件名',
    'editor_node_name' => '页面',
    'editor_sorting' => '页面排序',
    'editor_sort_by_url' => 'URL',
    'editor_sort_by_title' => '标题',
    'editor_sort_by_file_name' => '文件名',
    'editor_grouping' => '群组页面',
    'editor_group_by_filepath' => '文件路径',
    'editor_group_by_url' => 'URL',
    'editor_display' => '显示',
    'editor_display_title' => '标题',
    'editor_display_url' => 'URL',
    'editor_display_file' => '文件路径',
    'editor_markup' => '标记',
    'editor_code' => '代码',
    'description_hint' => '描述是可选的，仅在后端用户界面中可见。',
    'create_new' => '新页面',
  ],
  'layout' => [
    'not_found_name' => '布局 \':name\' 找不到',
    'menu_label' => '布局',
    'unsaved_label' => '未保存布局',
    'no_list_records' => '找不到布局',
    'new' => '新布局',
    'delete_confirm_multiple' => '您真的想要删除选中的布局?',
    'delete_confirm_single' => '您真的想要删除这个布局?',
    'editor_node_name' => '布局',
    'create_new' => '新布局',
  ],
  'partial' => [
    'not_found_name' => '部件 \':name\' 找不到.',
    'invalid_name' => '非法的部件名: :name.',
    'menu_label' => '部件',
    'unsaved_label' => '未保存的部件',
    'no_list_records' => '无法找到部件',
    'delete_confirm_multiple' => '您真的想要删除选择的部件?',
    'delete_confirm_single' => '您真的想要删除这个部件?',
    'editor_node_name' => '部件',
    'new' => '新的部件',
    'create_new' => '新建部件',
  ],
  'content' => [
    'not_found_name' => '无法找到内容文件 \':name\'',
    'menu_label' => '内容',
    'unsaved_label' => '未保存内容',
    'no_list_records' => '无法找到内容文件',
    'delete_confirm_multiple' => '您真的想要删除选中的文件或目录吗?',
    'delete_confirm_single' => '您真的想要删除这个内容文件?',
    'editor_node_name' => '内容文件',
    'new' => '新内容文件',
    'editor_content' => '内容',
  ],
  'ajax_handler' => [
    'invalid_name' => '非法 AJAX 处理器: :name.',
    'not_found' => '无法找到 AJAX 处理器 \':name\' ',
  ],
  'cms' => [
    'menu_label' => '内容管理系统',
  ],
  'sidebar' => [
    'add' => '增加',
    'search' => '搜索...',
  ],
  'editor' => [
    'settings' => '设置',
    'title' => '标题',
    'new_title' => '新页面标题',
    'url' => '网址',
    'filename' => '文件名',
    'layout' => 'CMS 布局',
    'description' => '说明',
    'preview' => '预览',
    'page' => '内容管理页面',
    'edit_theme' => '编辑主题',
    'change_edit_theme' => '更改编辑主题',
    'edit_theme_saved_changed_tabs' => '您在打开的选项卡中有未保存的更改。请保存它们或关闭标签以继续。',
    'partial' => 'CMS 部分',
    'meta' => 'Meta',
    'meta_title' => 'Meta标题',
    'meta_description' => 'Meta描述',
    'markup' => '标记',
    'code' => '代码',
    'content' => 'CMS 内容文件',
    'asset' => '资产文件',
    'hidden' => '隐藏',
    'hidden_comment' => '隐藏页面只能由登录的后端用户访问。',
    'enter_fullscreen' => '进入全屏模式',
    'exit_fullscreen' => '退出全屏模式',
    'open_searchbox' => '打开搜索框',
    'open_replacebox' => '打开替换框',
    'commit' => '提交',
    'reset' => '重置',
    'commit_confirm' => '您是否确认保存对文件的修改?这将会对原有的文件内容进行覆盖',
    'reset_confirm' => '您是否确认重置对文件的修改?这将会完全恢复文件到原来的内容',
    'committing' => '提交中...',
    'resetting' => '重置中...',
    'commit_success' => ' :type 保存成功',
    'reset_success' => ' :type 重置成功',
    'error_loading_header' => '加载模板时出错',
    'component_list' => '组件',
    'component_list_description' => '要添加一个组件，点击它或拖放到标记编辑器。',
    'info' => '信息',
    'refresh' => '刷新',
    'create' => '创建',
    'manage_themes' => '管理主题',
    'error_no_doctype_permissions' => '您没有权限管理文档类型：:doctype',
  ],
  'asset' => [
    'menu_label' => '资产',
    'unsaved_label' => '未保存的资源',
    'drop_down_add_title' => '添加...',
    'drop_down_operation_title' => '动作...',
    'upload_files' => '上传文件',
    'create_file' => '创建文件',
    'create_directory' => '新建目录',
    'directory_popup_title' => '新建目录',
    'directory_name' => '目录名',
    'directory_name_required' => '目录名必填',
    'rename_name_required' => '名称为必填',
    'rename' => '重命名',
    'delete' => '删除',
    'move' => '移动',
    'moving' => '移动所选项目',
    'moved' => '移动成功',
    'saved' => '文件已保存',
    'deleted' => '文件已删除',
    'select' => '选择',
    'new' => '新文件',
    'rename_popup_title' => '重命名',
    'rename_new_name' => '新名称',
    'invalid_path' => '路径只能包含数字、拉丁字母、空格和以下符号：._-@/',
    'error_deleting_file' => '删除文件时出错 :name。',
    'error_deleting_dir_not_empty' => '删除目录时出错 :name。目录不为空。',
    'error_deleting_dir' => '删除目录时出错 :name。',
    'invalid_name' => '名称只能包含数字、拉丁字母、空格和以下符号：._-@',
    'original_not_found' => '未找到原始文件或目录',
    'already_exists' => '同名的文件或目录已经存在',
    'error_renaming' => '重命名文件或目录时出错',
    'name_cant_be_empty' => '名称不能为空',
    'type_not_allowed' => '只允许以下文件类型: :allowed_types',
    'error_uploading_file' => '上传文件 \':name\' 时出错：:error',
    'move_please_select' => '请选择',
    'move_destination' => '目标目录',
    'move_popup_title' => '移动资源',
    'move_button' => '移动',
    'no_list_records' => '没有找到文件',
    'path' => '路径',
    'editor_node_name' => '资产',
    'open' => '打开',
  ],
  'component' => [
    'menu_label' => '组件',
    'unnamed' => '未命名',
    'no_description' => '没有描述',
    'alias' => '别名',
    'alias_description' => '在页面或者布局代码中组件的唯一名称',
    'validation_message' => '需要组件别名, 且只能包含拉丁字符, 数字和下划线. 别名必须以拉丁字符开头.',
    'invalid_request' => '组件数据非法，无法保存',
    'no_records' => '无法找到找不到',
    'not_found' => '无法找到组件 \':name\'',
    'method_not_found' => '组件 \':name\' 中无方法 \':method\'.',
    'expand_or_collapse' => '展开或折叠组件列表',
    'remove' => '移除组件',
    'expand_partial' => '展开组件部分',
  ],
  'template' => [
    'invalid_type' => '未知模板类型。',
    'not_found' => '未找到模板。',
    'saved' => '模板已保存',
    'saved_to_db' => '模板保存到数据库',
    'file_updated' => '模板文件已更新',
    'reset_from_template_success' => '模板已从文件中重置',
    'reloaded' => '重新加载模板',
    'deleted' => '模板已删除',
    'no_list_records' => '没有找到记录',
    'delete_confirm' => '删除选定的模板？',
    'order_by' => '排序',
    'last_modified' => '最后修改',
    'storage' => '存储',
    'template_file' => '模板文件',
    'storage_filesystem' => '文件系统',
    'storage_database' => '数据库',
    'update_file' => '更新模板文件',
    'reset_from_file' => '从模板文件重置',
  ],
  'permissions' => [
    'name' => 'CMS',
    'manage_content' => '管理内容',
    'manage_assets' => '管理资源',
    'manage_pages' => '管理页面',
    'manage_layouts' => '管理布局',
    'manage_partials' => '管理部件',
    'manage_themes' => '管理主题',
    'manage_theme_options' => '管理主题的自定义选项',
  ],
  'theme_log' => [
    'hint' => '显示管理员在后台对主题的所有操作日志',
    'menu_label' => '主题操作日志',
    'menu_description' => '查看对激活主题的操作日志.',
    'empty_link' => '清空操作日志',
    'empty_loading' => '清空主题操作日志中...',
    'empty_success' => '主题操作日志清空成功',
    'return_link' => '返回主题操作日志',
    'id' => '序号',
    'id_label' => '日志 序号',
    'created_at' => '日志生成时间',
    'user' => '用户名',
    'type' => '操作类型',
    'type_create' => '创建',
    'type_update' => '更新',
    'type_delete' => '删除',
    'theme_name' => '主题名',
    'theme_code' => '主题code',
    'old_template' => '文件名 (旧)',
    'new_template' => '文件名 (新)',
    'template' => '文件名',
    'diff' => '文件修改对比',
    'old_value' => '文件修改前',
    'new_value' => '文件修改后',
    'preview_title' => '文件修改详情',
    'template_updated' => '文件已更新',
    'template_created' => '文件已创建',
    'template_deleted' => '文件已删除',
  ],
  'intellisense' => [
    'learn_more' => '了解更多',
    'docs' => [
      'partial' => '渲染 CMS 部分的内容。',
      'page' => '将 CMS 页面的内容呈现到布局模板中。',
      'content' => '渲染一个 CMS 内容块。',
      'component' => '渲染 CMS 组件的默认标记内容。',
      'placeholder' => '渲染一个占位符部分。',
      'scripts' => '向应用程序注入的脚本插入 JavaScript 文件引用。',
      'styles' => '渲染 CSS 链接到应用程序注入的样式表文件。',
      'flash' => '渲染存储在用户会话中的任何 flash 消息。',
      'verbatim' => '将整个部分标记为不应被解析的原始文本。',
      'macro' => '允许在模板中定义自定义函数。',
      'for' => '循环遍历集合中的每个值。',
      'if' => '允许有条件地显示模板标记。',
      'abs_filter' => 'abs 过滤器返回绝对值。',
      'batch_filter' => 'batch 过滤器通过返回具有给定项目数的列表列表来“批处理”项目。可以提供第二个参数并用于填充缺失的项目。',
      'capitalize_filter' => '`capitalize` 过滤器将一个值设为大写。第一个字符将大写，所有其他字符小写。',
      'column_filter' => '`column` 过滤器返回输入数组中单列的值。',
      'convert_encoding_filter' => '`convert_encoding` 过滤器将字符串从一种编码转换为另一种编码。第一个参数是预期的输出字符集，第二个参数是输入字符集。',
      'country_name_filter' => '`country_name` 过滤器根据 ISO-3166 两字母代码返回国家名称。',
      'currency_name_filter' => '`currency_name` 过滤器根据三个字母的代码返回货币名称。',
      'currency_symbol_filter' => '`currency_symbol` 过滤器返回给定三字母代码的货币符号。',
      'data_uri_filter' => '`data_uri` 过滤器使用 RFC 2397 中定义的数据方案生成一个 URL。',
      'date_filter' => 'date 过滤器将日期格式化为给定的格式。',
      'date_modify_filter' => '`date_modify` 过滤器使用给定的修饰符字符串修改日期。',
      'default_filter' => '如果值未定义或为空，`default` 过滤器返回传递的默认值，否则返回变量的值。',
      'escape_filter' => '`escape` 过滤器使用依赖于上下文的策略对字符串进行转义。',
      'filter_filter' => '过滤器过滤器使用箭头函数过滤序列或映射的元素。箭头函数接收序列或映射的值。',
      'first_filter' => 'first 过滤器返回序列、映射或字符串的第一个“元素”。',
      'format_filter' => '`format` 过滤器通过替换占位符来格式化给定的字符串（占位符遵循 [sprintf](https://www.php.net/sprintf) 符号）。',
      'format_currency_filter' => '`format_currency` 过滤器将数字格式化为货币。',
      'join_filter' => 'join 过滤器返回一个字符串，它是一个序列项目的串联',
      'json_encode_filter' => '`json_encode` 过滤器返回一个值的 JSON 表示。',
      'keys_filter' => '`keys` 过滤器返回数组的键。当您想遍历数组的键时，它很有用。',
      'last_filter' => '`last` 过滤器返回序列、映射或字符串的最后一个“元素”。',
      'length_filter' => '`length` 过滤器返回序列或映射的项数，或字符串的长度。',
      'lower_filter' => '`lower` 过滤器将值转换为小写。',
      'map_filter' => 'map 过滤器将箭头函数应用于序列或映射的元素。箭头函数接收序列或映射的值。',
      'merge_filter' => '`merge` 过滤器将一个数组与另一个数组合并。',
      'nl2br_filter' => '`nl2br` 过滤器在字符串中的所有换行符之前插入 HTML 换行符。',
      'number_format_filter' => 'number_format 过滤器格式化数字。它是 PHP 的 [number_format](https://www.php.net/number_format) 函数的包装器。',
      'reduce_filter' => '`reduce`过滤器使用箭头函数迭代地将序列或映射减少到单个值，从而将其减少到单个值。箭头函数接收前一次迭代的返回值和序列或映射的当前值。',
      'replace_filter' => '`replace` 过滤器通过替换占位符来格式化给定的字符串。',
      'reverse_filter' => '`reverse` 过滤器反转序列、映射或字符串。',
      'round_filter' => '`round` 过滤器将数字四舍五入到给定的精度。',
      'slice_filter' => '`slice` 过滤器提取序列、映射或字符串的切片。',
      'sort_filter' => '`sort` 过滤器对数组进行排序。',
      'spaceless_filter' => '使用 `spaceless` 过滤器去除 HTML 标签之间的空白，而不是 HTML 标签内的空白或纯文本中的空白。',
      'split_filter' => '`split` 过滤器通过给定的分隔符分割一个字符串并返回一个字符串列表。',
      'striptags_filter' => '`striptags` 过滤器去除 SGML/XML 标签并用一个空格替换相邻的空格。',
      'title_filter' => '`title` 过滤器返回值的标题版本。单词将以大写字母开头，其余所有字符均为小写。',
      'trim_filter' => '`trim` 过滤器从字符串的开头和结尾去除空格（或其他字符）。',
      'upper_filter' => '`upper` 过滤器将一个值转换为大写。',
      'url_encode_filter' => '`url_encode` 过滤器百分比将给定的字符串编码为 URL 段或将数组编码为查询字符串。',
      'page_filter' => '`page` 过滤器使用页面文件名创建一个指向页面的链接，没有扩展名，作为参数。',
      'theme_filter' => '`theme` 过滤器返回一个相对于网站活动主题路径的地址。 ',
      'app_filter' => '`app` 过滤器返回一个相对于网站公共路径的地址。',
      'media_filter' => '`media` 过滤器返回一个相对于 [媒体管理器库](https://octobercms.com/docs/cms/mediamanager) 公共路径的地址。 ',
      'md_filter' => '`md` 过滤器将值从 Markdown 转换为 HTML 格式。',
      'raw_filter' => '`raw` 过滤器将值标记为“安全”，这意味着如果`raw` 是最后一个应用于它的过滤器，则该变量不会被转义。',
    ],
  ],
];