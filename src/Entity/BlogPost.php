<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;


/**
 * BlogPost
 *
 * @ORM\Table(name="blog_post")
 * @ORM\Entity(repositoryClass="App\Repository\BlogPostRepository")
 * @ORM\HasLifecycleCallbacks
 */
class BlogPost
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255,unique=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="writer", type="string", length=255)
     */
    private $writer;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=255)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, unique=true, nullable=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=2000)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="cover", type="text")
     */
    private $cover;

    /**
     * @var Author
     *
     * @ORM\ManyToOne(targetEntity="Author")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="review", type="text")
     */
    private $review;

    /**
     * @OneToMany(targetEntity="Comment", mappedBy="blogPost")
     */
    private $comments;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;



    public function __construct() {
        $this->comments = new ArrayCollection();
    }

    /**
     * Get comments
     *
     * @return Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Comment $comments
     * @return $this
     */
    public function addComments(Comment $comments)
    {
        $this->comments[] = $comments;
        return $this;
    }



    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return BlogPost
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set writer
     *
     * @param string $writer
     *
     * @return BlogPost
     */
    public function setWriter($writer)
    {
        $this->writer = $writer;

        return $this;
    }

    /**
     * Get writer
     *
     * @return string
     */
    public function getWriter()
    {
        return $this->writer;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }


    /**
     * set $category
     *
     * @param string $category
     *
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }



    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return BlogPost
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return BlogPost
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set cover
     *
     * @param string $cover
     *
     * @return BlogPost
     */
    public function setCover($cover)
    {
        $this->cover = $cover;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Set author
     *
     * @param Author $author
     *
     * @return BlogPost
     */
    public function setAuthor(Author $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return Author
     */
    public function getAuthor()
    {
        return $this->author;
    }
    /**
     * Set review
     *
     * @param string $review
     *
     * @return BlogPost
     */
    public function setReview($review)
    {
        $this->review = $review;

        return $this;
    }

    /**
     * Get review
     *
     * @return string
     */
    public function getReview()
    {
        return $this->review;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return BlogPost
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return BlogPost
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt(new \DateTime());
        }

        if (!$this->getUpdatedAt()) {
            $this->setUpdatedAt(new \DateTime());
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    }