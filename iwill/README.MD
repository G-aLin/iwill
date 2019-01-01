### 头部
- 不固定定位，不浮动定位
	` header`

- 浮动定位
	`header fixed finish`
	`div.header`后面要加`div.header-box`

- 首页固定定位 + 浮动定位
	`header header-abs`

### 登陆注册
- 显示登陆，隐藏注册
	```
	&.signIn
		display: block
	&.register
		display: none
	```
- 显示注册，隐藏登陆
	```
	&.signIn
		display: none
	&.register
		display: block
	```
- 验证错误
  - 在外层加类名`shake err`