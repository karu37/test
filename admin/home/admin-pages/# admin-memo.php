<?/*

//-----------------------------------
ALine - ���� ���޵Ǵ� ����
//-----------------------------------

// ----------------------------------------------
* SQL�ۼ��� �Ʒ��� Table NickName ������ ��
  WHERE ���� �ۼ��� 1�������� �ۼ��� �� �ֵ��� �ϱ� ����.
// ----------------------------------------------

# ON/OFF flags �����ϴ� ���̺� ���
	al_partner_t.is_mactive			: �����ڰ� Partner ID Ȱ��/����/���� ( Y/N/D )
	
	al_merchant_t.is_mactive		: �����ڰ� Merchant Code Ȱ��/����/���� ( Y/N/D )
	al_publisher_t.is_mactive		: �����ڰ� Publisher Code Ȱ��/����/���� ( Y/N/D )
	
	al_app_t.is_mactive				: �����ڰ� �ش� ���� Ȱ��/����/���� ( Y/N/D )
	al_app_t.is_active				: [Merchant]�� �ش� ���� Ȱ��/����
	
	al_publisher_app_t.publisher_active : [Publisher]�� ���� �ޱ⸦ Ȱ��/���� ( Y/N )
	al_publisher_app_t.is_mactive 	: �����ڰ� Publisher���� app���� Ȱ��/����/���� ( Y/N/D )
	al_publisher_app_t.is_prohibit  : is_public_mode = Y �� ��� ��� �׷��� is_prohibit = Y �� �̰��� ����
	al_publisher_app_t.is_allow		: is_public_mode = N �� ��� ���� �׷��� is_allow = Y �� �̰��� ���
		by	al_app_t.is_public_mode			: �����ڰ� ���� ( Y/N )
		
	
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
	
		-- �����̺� ������ ���� al_publisher_app_t.publisher_active : [Publisher]�� ���� �ޱ⸦ Ȱ��/���� ( Y/N )
	
	al_publisher_app_t.is_mactive 	: �����ڰ� Publisher���� app���� Ȱ��/����/���� ( Y/N/D )
	al_publisher_app_t.is_prohibit  : is_public_mode = Y �� ��� ��� �׷��� is_prohibit = Y �� �̰��� ����
	al_publisher_app_t.is_allow		: is_public_mode = N �� ��� ���� �׷��� is_allow = Y �� �̰��� ���
		by	al_app_t.is_public_mode			: �����ڰ� ���� ( Y/N )

-- ���� ��ü ���� �ð� ���� (�Ʒ������� ��� AND)

 	al_publisher_app_t.open_time			: �����ڰ� ������ - �ش� Publisher & ���� ���/����
 	
 	al_app_t.exec_sdate ~ exec_edate
 	al_app_t.exec_stime ~ exec_etime
	al_app_t.exec_hour_max_cnt <vs> al_app_exec_stat_t.live_exec_tmcnt
	
	al_publisher_app_t.exec_day_max_cnt or al_app_t.exec_day_max_cnt <vs> al_app_exec_stat_t.live_exec_cnt
 	al_publisher_app_t.exec_tot_max_cnt or al_app_t.exec_tot_max_cnt <vs> al_app_exec_stat_t.live_exec_totcnt

	# ���͸� ������ Publisher���� �״�� �����Ѵ�.
		al_app_t.app_gender, app_agefrom, app_ageto

	# al_user_app_t �� ���� �Ϸ�/�Ұ� ��� �����ؼ� 

*/?>