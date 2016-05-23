<?/*

//-----------------------------------
ALine - ����ȯ�� ����
//-----------------------------------

127.0.0.1		admin.aline-soft.kr image.aline-soft.kr
127.0.0.1		www.aline-soft.kr aline-soft.kr partner.aline-soft.kr

<VirtualHost *:80>
	ServerName admin.aline-soft.kr
	ServerAlias admin.aline-soft.kr
	ServerAdmin a@localhost
	DocumentRoot "D:\Develope_Web\www.aline-soft.kr\admin\home"
	
	<Directory />
		Options FollowSymLinks
		AllowOverride FileInfo
		Require all granted
	</Directory>	
</VirtualHost>

<VirtualHost *:80>
	ServerName image.aline-soft.kr
	ServerAlias image.aline-soft.kr
	ServerAdmin a@localhost
	DocumentRoot "D:\Develope_Web\www.aline-soft.kr\image\home"

	<Directory />
		Options FollowSymLinks
		AllowOverride FileInfo
		Require all granted
	</Directory>	
</VirtualHost>

//-----------------------------------
ALine - ���� ���޵Ǵ� ����
//-----------------------------------

// ----------------------------------------------
* SQL�ۼ��� �Ʒ��� Table NickName ������ ��
  WHERE ���� �ۼ��� 1�������� �ۼ��� �� �ֵ��� �ϱ� ����.
// ----------------------------------------------

# ON/OFF flags �����ϴ� ���̺� ���
	al_partner_t.is_mactive						: [������]�� Partner ID Ȱ��/����/���� ( Y/N/D )
	
	1�� al_app_t.is_mactive						: [������]�� �ش� ���� Ȱ��/����/���� ( Y/N/D )
	2�� al_app_t.is_active						: [Merchant]�� �ش� ���� Ȱ��/���� ( Y/N )
	
	3�� al_merchant_t.is_mactive				: [������]�� Merchant Code Ȱ��/�׽�Ʈ/����/���� ( Y/T/N/D )
	4�� al_publisher_t.is_mactive				: [������]�� Publisher Code Ȱ��/�׽�Ʈ/����/���� ( Y/T/N/D )
	
	5�� al_publisher_app_t.is_mactive 			: [������]�� Publisher���� app���� Ȱ��/����/���� ( Y/N/D )
	6�� al_publisher_app_t.publisher_disabled 	: [Publisher]�� ���� �ޱ⸦ ���� ( Y )

	7�� al_app_t.publisher_level				: Publisher ���� ���� ����
			al_publisher_t.level				: 	app�� ���޷������� ���� ���(���ڷδ� �������) ���� ����
		
	8�� al_app_t.is_public_mode					: [Merchant]�� public ��� ����
			al_publisher_app_t.merchant_disabled: is_public_mode = Y�� ��� ������ 'N'�̸� ����
			al_publisher_app_t.merchant_enabled	: is_public_mode = N�� ��� ������ 'Y'�̸� ����

	-- ���� ��ü ���� �ð� ���� (�Ʒ������� ��� AND)

	 	al_app_t.exec_stime ~ exec_etime		: ���� ������ ���� ���� �ð�
	
	 	al_publisher_app_t.active_time			: ���� Ȱ�� �ð� - �����ڰ� ������ - �ش� Publisher & ���� ���/����
	 	
	 	al_app_t.exec_edate						: end �ð����� ������ ������ ����
		
		al_publisher_app_t.exec_hour_max_cnt or al_app_t.exec_hour_max_cnt <vs> al_app_exec_stat_t.exec_hour_cnt
		al_publisher_app_t.exec_day_max_cnt or al_app_t.exec_day_max_cnt <vs> al_app_exec_stat_t.exec_day_cnt
	 	al_publisher_app_t.exec_tot_max_cnt or al_app_t.exec_tot_max_cnt <vs> al_app_exec_stat_t.exec_tot_cnt
	
		# ���͸� ������ Publisher���� �״�� �����Ѵ�.
			al_app_t.app_gender, app_agefrom, app_ageto
	
		# al_user_app_t �� ���� �Ϸ�/�Ұ� ��� �����ؼ� 
	
# Table NICK - a,b,c,d ������ Reserved
	al_app_t			v
	al_publisher_app_t	pa
	al_merchant_t		m
	al_publisher_t		p
	al_app_exec_stat_t	e
	al_user_app_t		u


# Publisher���� ���� ���ý�

	al_merchant_t.is_mactive		: �����ڰ� Merchant Code Ȱ��/����/���� ( Y/N/D )
	al_publisher_t.is_mactive		: �����ڰ� Publisher Code Ȱ��/����/���� ( Y/N/D )
	
	al_app_t.is_mactive				: �����ڰ� �ش� ���� Ȱ��/����/���� ( Y/N/D )
	al_app_t.is_active				: [Merchant]�� �ش� ���� Ȱ��/����

	al_publisher_app_t.is_mactive 	: �����ڰ� Publisher���� app���� Ȱ��/����/���� ( Y/N/D )
	
		-- �����̺� ������ ���� al_publisher_app_t.publisher_active : [Publisher]�� ���� �ޱ⸦ Ȱ��/���� ( Y/N )
	
	al_app_t.is_public_mode					: [Merchant]�� public ��� ����
	al_publisher_app_t.merchant_disabled: is_public_mode = Y�� ��� ������ 'N'�̸� ����
	al_publisher_app_t.merchant_enabled	: is_public_mode = N�� ��� ������ 'Y'�̸� ����
	


*/?>