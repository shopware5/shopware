<?php
namespace   Shopware\CustomModels\Staging;
use         Shopware\Components\Model\ModelRepository;

class Repository extends ModelRepository
{
    public function getListQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        $builder = $this->getListQueryBuilder($filter, $order);

        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    public function getListQueryBuilder($profileId,$filter = "")
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('tables'))
                ->from('Shopware\CustomModels\Staging\Tables', 'tables')
                ->where('tables.profileId = ?1')
                ->orderBy("tables.tableName","ASC")
                ->setParameter(1,$profileId);
        if (!empty($filter)){
            $builder->andWhere("tables.tableName LIKE ?2");
            $builder->setParameter(2,"%".$filter."%");
        }
        return $builder;
    }



    public function deleteColumnAssignments($tableId){
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete()
        ->from('Shopware\CustomModels\Staging\Columns', 'columns')
        ->where('columns.tableId = ?1')
        ->setParameter(1,$tableId);
        return $builder;
    }

    public function deleteJobQueue($jobId){
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete()
        ->from('Shopware\CustomModels\Staging\Queue', 'queue')
        ->where('queue.jobId = ?1')
        ->setParameter(1,$jobId);
        //$builder->getQuery()->execute()
        return $builder;
    }

    public function getJobType($profileId){
           $builder = Shopware()->Models()->createQueryBuilder();
           $builder->select(array("profiles.profileAssignment"))
           ->from('Shopware\CustomModels\Staging\Profiles', 'profiles')
           ->where('profiles.id = ?1')
           ->setParameter(1,$profileId);
           return $builder;
        }
    public function getJobsQueryBuilder(){
        $builder = Shopware()->Models()->createQueryBuilder();

        $builder->select(array('jobs','profile.profileAssignment'))
                ->from('Shopware\CustomModels\Staging\Jobs', 'jobs')
                ->from('Shopware\CustomModels\Staging\Profiles','profile')
                ->where('jobs.profileId = profile.id')
                ->orderBy("jobs.id","DESC");

        return $builder;
    }

    public function getQueueQueryBuilder($jobId,$onlyOpen=false,$limit=false){
        $builder = Shopware()->Models()->createQueryBuilder();

        $builder->select(array('queue'))
                ->from('Shopware\CustomModels\Staging\Queue', 'queue')
                ->where('queue.jobId = ?1')
                ->orderBy("queue.id","ASC")
                ->setParameter(1,$jobId);
        if ($onlyOpen == true){
            $builder->andWhere("queue.done = 0");
        }
        if ($limit !==false){
            $builder->setMaxResults($limit);
        }
        return $builder;
    }

    public function getQueueTotalCount($jobId,$onlyOpen=false){
       $builder = Shopware()->Models()->createQueryBuilder();
       $builder->select(array('COUNT(queue.id)'))
               ->from('Shopware\CustomModels\Staging\Queue', 'queue')
               ->where('queue.jobId = ?1')
               ->setParameter(1,$jobId);
       if ($onlyOpen == true){
           $builder->andWhere("queue.done = 0");
       }
       return $builder;
    }

    public function getProfilesQueryBuilder($filter = ""){
        $builder = Shopware()->Models()->createQueryBuilder();

        $builder->select(array('profiles'))
                  ->from('Shopware\CustomModels\Staging\Profiles', 'profiles');
        if (!empty($filter)){
            $builder->where("profiles.profileAssignment = ?1");
            $builder->setParameter(1,$filter);
        }
        return $builder;
    }
}