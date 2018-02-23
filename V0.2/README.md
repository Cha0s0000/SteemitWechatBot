# SteemitWechatBot   V0.2

---

## New features

- Mention when get the comment
- Use Workerman framework to get the related data ,which is convenient for extending in the future
- Use Wechat public account instead of the Wechat private account.That  can become more and more expansible .As we all know ,wechat private account may be prohibited  using as bot.So choose the Wechat public account is necessary.



---

## How to deploy

- **Install as the first version**

-  **SteemitWechatBot/V0.2/Python bot/main.py**

  ![图片.png](https://res.cloudinary.com/hpiynhbhq/image/upload/v1519395504/cvpt10rdsq3n4tslujbi.png)

  In line 37 ,replace my Steemit name with yours.

  ![图片.png](https://res.cloudinary.com/hpiynhbhq/image/upload/v1519395555/kjmudtwssbtkpjlejpfo.png)

  In line 40,change the TCP server ip with yours.

-  **SteemitWechatBot/V0.2/Workerman TCP Server/Applications/YourApp/Events.php**

  ![图片.png](https://res.cloudinary.com/hpiynhbhq/image/upload/v1519395638/dg2hfqa3a4tvebf0vxln.png)

  Use your wechat public account appid and appsecret;

  ![图片.png](https://res.cloudinary.com/hpiynhbhq/image/upload/v1519395639/vc9eqpw0ndghx7rqptn2.png)

  Use your user openid and template id ;

  ![图片.png](https://res.cloudinary.com/hpiynhbhq/image/upload/v1519395650/gxyx1t6e9z0r5dkbr2ib.png)

  Change with your template information